<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function record(
        Debt $debt,
        int $amountMinor,
        CarbonInterface $paidOn,
        ?string $note,
        User $createdBy,
        string $submissionToken,
    ): Payment {
        return DB::transaction(function () use ($debt, $amountMinor, $paidOn, $note, $createdBy, $submissionToken): Payment {
            $existing = Payment::query()->where('submission_token', $submissionToken)->first();

            if ($existing !== null) {
                return $existing;
            }

            $lockedDebt = Debt::query()->lockForUpdate()->findOrFail($debt->id);

            if ($amountMinor < 1) {
                throw ValidationException::withMessages(['amount' => 'Uplata mora biti veća od nule.']);
            }

            if ($paidOn->lt($lockedDebt->incurred_on) || $paidOn->isFuture()) {
                throw ValidationException::withMessages(['paid_on' => 'Datum uplate mora biti između datuma zaduženja i današnjeg dana.']);
            }

            if ($amountMinor > $lockedDebt->outstandingAmountMinor()) {
                throw ValidationException::withMessages(['amount' => 'Uplata ne može biti veća od preostalog duga.']);
            }

            $payment = $lockedDebt->payments()->create([
                'amount_minor' => $amountMinor,
                'paid_on' => $paidOn->toDateString(),
                'note' => $note,
                'created_by' => $createdBy->id,
                'submission_token' => $submissionToken,
            ]);

            AuditLog::query()->create([
                'user_id' => $createdBy->id,
                'action' => 'payment.created',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'metadata' => ['debt_id' => $lockedDebt->id, 'amount_minor' => $amountMinor],
            ]);

            return $payment;
        });
    }

    public function void(Payment $payment, User $actor, string $reason): Payment
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Samo administrator može stornirati uplatu.');
        }

        return DB::transaction(function () use ($payment, $actor, $reason): Payment {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->voided_at !== null) {
                throw new DomainException('Uplata je već stornirana.');
            }

            if (trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'Razlog storna je obavezan.']);
            }

            $lockedPayment->forceFill([
                'voided_at' => now(),
                'voided_by' => $actor->id,
                'void_reason' => $reason,
            ])->save();

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'payment.voided',
                'auditable_type' => Payment::class,
                'auditable_id' => $lockedPayment->id,
                'metadata' => ['reason' => $reason, 'debt_id' => $lockedPayment->debt_id],
            ]);

            return $lockedPayment->fresh();
        });
    }
}

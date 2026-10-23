<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtorDebtRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\VoidPaymentRequest;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DebtorController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,settled'],
        ]);
        $year = (int) ($validated['year'] ?? now()->year);
        $search = trim((string) ($validated['search'] ?? ''));
        $status = $validated['status'] ?? null;
        $start = "{$year}-01-01";
        $end = "{$year}-12-31";

        $debtQuery = static function (Builder|Relation $query) use ($start, $end): void {
            $query->whereBetween('incurred_on', [$start, $end]);
        };

        $debtors = Debtor::query()
            ->visibleTo($request->user())
            ->whereHas('debts', $debtQuery)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = "%{$search}%";
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($status !== null, function (Builder $query) use ($start, $end, $status): void {
                $operator = $status === 'settled' ? '=' : '>';

                $query->whereHas('debts', function (Builder $query) use ($start, $end, $operator): void {
                    $query->whereBetween('incurred_on', [$start, $end])
                        ->whereRaw(
                            "original_amount_minor - (SELECT COALESCE(SUM(amount_minor), 0) FROM payments WHERE payments.debt_id = debts.id AND payments.voided_at IS NULL) {$operator} 0",
                        );
                });
            })
            ->with(['debts' => function (Builder|Relation $query) use ($start, $end): void {
                $query->whereBetween('incurred_on', [$start, $end])
                    ->withSum(['payments as paid_amount_minor' => function (Builder $query): void {
                        $query->whereNull('voided_at');
                    }], 'amount_minor');
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $debtors->getCollection()->transform(function (Debtor $debtor): Debtor {
            $original = (int) $debtor->debts->sum('original_amount_minor');
            $paid = (int) $debtor->debts->sum(fn (Debt $debt): int => (int) ($debt->paid_amount_minor ?? 0));
            $debtor->setAttribute('annual_original_minor', $original);
            $debtor->setAttribute('annual_paid_minor', $paid);
            $debtor->setAttribute('annual_outstanding_minor', max(0, $original - $paid));
            $debtor->setAttribute('annual_status', $original - $paid === 0 ? 'settled' : 'active');

            return $debtor;
        });

        $originalTotal = (int) (Debt::query()->visibleTo($request->user())->forYear($year)->sum('original_amount_minor'));
        $paidTotal = (int) Payment::query()
            ->whereNull('voided_at')
            ->whereHas('debt', fn (Builder $query): Builder => $query->visibleTo($request->user())->whereBetween('incurred_on', [$start, $end]))
            ->sum('amount_minor');
        $years = Debt::query()->visibleTo($request->user())
            ->select('incurred_on')
            ->distinct()
            ->orderByDesc('incurred_on')
            ->get()
            ->map(fn (Debt $debt): int => $debt->incurred_on->year)
            ->unique()
            ->values();

        return view('debtors.index', [
            'debtors' => $debtors,
            'year' => $year,
            'years' => $years,
            'search' => $search,
            'status' => $status,
            'originalTotal' => $originalTotal,
            'paidTotal' => $paidTotal,
            'outstandingTotal' => max(0, $originalTotal - $paidTotal),
        ]);
    }

    public function create(Request $request): View
    {
        return view('debtors.create', [
            'debtors' => Debtor::query()->visibleTo($request->user())->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function show(Debtor $debtor, Request $request): View
    {
        $debtor = Debtor::query()->visibleTo($request->user())->findOrFail($debtor->id);
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $selectedYear = isset($validated['year']) ? (int) $validated['year'] : null;
        $debts = $debtor->debts()
            ->with(['payments' => fn ($query) => $query->with(['creator', 'voidedBy'])->latest('paid_on')->latest('id')])
            ->orderByDesc('incurred_on')
            ->orderByDesc('id')
            ->get();

        $debtsByYear = $debts->groupBy(fn (Debt $debt): int => $debt->incurred_on->year);
        $totalOriginal = (int) $debts->sum('original_amount_minor');
        $totalPaid = (int) $debts->sum(fn (Debt $debt): int => $debt->paidAmountMinor());

        return view('debtors.show', [
            'debtor' => $debtor,
            'debtsByYear' => $debtsByYear,
            'selectedYear' => $selectedYear,
            'totalOriginal' => $totalOriginal,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => max(0, $totalOriginal - $totalPaid),
        ]);
    }

    public function storePayment(Debtor $debtor, StorePaymentRequest $request, PaymentService $paymentService): RedirectResponse
    {
        $debtor = Debtor::query()->visibleTo($request->user())->findOrFail($debtor->id);
        $data = $request->validated();
        $debt = $debtor->debts()->findOrFail($data['debt_id']);

        $paymentService->record(
            $debt,
            $request->amountMinor(),
            CarbonImmutable::parse($data['paid_on']),
            $data['note'] ?? null,
            $request->user(),
            $data['submission_token'],
        );

        return redirect()->route('debtors.show', ['debtor' => $debtor, 'year' => $debt->incurred_on->year])
            ->with('status', 'Uplata je uspešno evidentirana.');
    }

    public function voidPayment(Debtor $debtor, VoidPaymentRequest $request, PaymentService $paymentService): RedirectResponse
    {
        $debtor = Debtor::query()->visibleTo($request->user())->findOrFail($debtor->id);
        $data = $request->validated();
        $payment = $debtor->debts()->whereKey($data['debt_id'])->firstOrFail()
            ->payments()->findOrFail($data['payment_id']);

        $paymentService->void($payment, $request->user(), $data['reason']);

        return redirect()->route('debtors.show', ['debtor' => $debtor, 'year' => $payment->debt->incurred_on->year])
            ->with('status', 'Uplata je stornirana.');
    }

    public function store(StoreDebtorDebtRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $debt = DB::transaction(function () use ($data, $request): Debt {
            $storeId = $request->user()->store_id;

            if (! empty($data['debtor_id'])) {
                $debtor = Debtor::query()->visibleTo($request->user())->findOrFail($data['debtor_id']);
            } else {
                $debtor = Debtor::query()->create(Arr::only($data, [
                    'first_name',
                    'last_name',
                    'address',
                    'city',
                    'phone',
                ]) + ['store_id' => $storeId]);
            }

            return $debtor->debts()->create([
                'store_id' => $debtor->store_id ?? $storeId,
                'incurred_on' => $data['incurred_on'],
                'original_amount_minor' => $request->amountMinor(),
                'installment_count' => $data['installment_count'],
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('debtors.index', ['year' => $debt->incurred_on->year])
            ->with('status', 'Dužnik i zaduženje su uspešno sačuvani.');
    }
}

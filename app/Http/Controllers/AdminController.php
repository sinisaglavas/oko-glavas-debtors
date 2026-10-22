<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
        $year = (int) ($validated['year'] ?? now()->year);

        $start = "{$year}-01-01";
        $end = "{$year}-12-31";
        $debtQuery = static function (Builder $query) use ($start, $end): void {
            $query->whereBetween('incurred_on', [$start, $end]);
        };

        $originalTotal = (int) Debt::query()->forYear($year)->sum('original_amount_minor');
        $paidTotal = (int) Payment::query()->whereNull('voided_at')->whereHas('debt', $debtQuery)->sum('amount_minor');
        $activeDebtors = Debtor::query()->whereHas('debts', function (Builder $query) use ($start, $end): void {
            $query->whereBetween('incurred_on', [$start, $end])->whereRaw(
                'original_amount_minor - (SELECT COALESCE(SUM(amount_minor), 0) FROM payments WHERE payments.debt_id = debts.id AND payments.voided_at IS NULL) > 0',
            );
        })->count();
        $settledDebtors = Debtor::query()->whereHas('debts', function (Builder $query) use ($start, $end): void {
            $query->whereBetween('incurred_on', [$start, $end])->whereRaw(
                'original_amount_minor - (SELECT COALESCE(SUM(amount_minor), 0) FROM payments WHERE payments.debt_id = debts.id AND payments.voided_at IS NULL) = 0',
            );
        })->count();

        $recentPayments = Payment::query()
            ->with(['debt.debtor', 'creator'])
            ->latest('paid_on')->latest('id')->limit(8)->get();
        $recentLogs = AuditLog::query()->with('user')->latest()->limit(8)->get();

        return view('admin.dashboard', compact(
            'year', 'originalTotal', 'paidTotal', 'activeDebtors', 'settledDebtors', 'recentPayments', 'recentLogs',
        ) + ['outstandingTotal' => max(0, $originalTotal - $paidTotal)]);
    }

    public function audit(Request $request): View
    {
        $logs = AuditLog::query()->with('user')->latest()->paginate(30)->withQueryString();

        return view('admin.audit', compact('logs'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminDebtorRequest;
use App\Models\AuditLog;
use App\Models\Debtor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDebtorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $debtors = Debtor::query()
            ->withCount('debts')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')->orderBy('first_name')
            ->paginate(20)->withQueryString();

        return view('admin.debtors.index', compact('debtors', 'search'));
    }

    public function edit(Debtor $debtor): View
    {
        return view('admin.debtors.edit', compact('debtor'));
    }

    public function update(UpdateAdminDebtorRequest $request, Debtor $debtor): RedirectResponse
    {
        $before = $debtor->only(['first_name', 'last_name', 'address', 'city', 'phone']);
        $debtor->update($request->validated());

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'debtor.updated',
            'auditable_type' => Debtor::class,
            'auditable_id' => $debtor->id,
            'metadata' => ['before' => $before, 'after' => $debtor->only(array_keys($before))],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.debtors.index')->with('status', 'Podaci dužnika su izmenjeni.');
    }
}

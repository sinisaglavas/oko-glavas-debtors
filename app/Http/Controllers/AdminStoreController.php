<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminStoreRequest;
use App\Models\AuditLog;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::query()
            ->withCount(['users', 'debtors', 'debts'])
            ->orderBy('name')
            ->get();

        return view('admin.stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('admin.stores.create');
    }

    public function store(StoreAdminStoreRequest $request): RedirectResponse
    {
        $store = Store::query()->create($request->validated());

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'store.created',
            'auditable_type' => Store::class,
            'auditable_id' => $store->id,
            'metadata' => ['name' => $store->name, 'code' => $store->code],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.stores.index')->with('status', 'Optika je kreirana.');
    }

    public function toggle(Store $store, Request $request): RedirectResponse
    {
        $store->forceFill(['is_active' => ! $store->is_active])->save();

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => $store->is_active ? 'store.activated' : 'store.deactivated',
            'auditable_type' => Store::class,
            'auditable_id' => $store->id,
            'metadata' => ['code' => $store->code],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('status', $store->is_active ? 'Optika je aktivirana.' : 'Optika je deaktivirana.');
    }
}

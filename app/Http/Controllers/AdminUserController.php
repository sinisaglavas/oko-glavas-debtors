<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->validated());

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'user.created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'metadata' => ['email' => $user->email, 'is_admin' => $user->is_admin],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Korisnik je uspešno kreiran.');
    }

    public function toggle(User $user, Request $request): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Ne možete deaktivirati sopstveni nalog.');

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => $user->is_active ? 'user.activated' : 'user.deactivated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'metadata' => ['email' => $user->email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('status', $user->is_active ? 'Korisnik je aktiviran.' : 'Korisnik je deaktiviran.');
    }
}

@extends('layouts.app')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-indigo-700">← Admin panel</a>
            <h1 class="mt-3 text-2xl font-bold">Optike</h1>
            <p class="mt-1 text-slate-600">Svaka optika ima odvojene dužnike, dugove i izveštaje.</p>
        </div>
        <a href="{{ route('admin.stores.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Nova optika</a>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($stores as $store)
            <article class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold">{{ $store->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Oznaka: {{ $store->code }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $store->address ?: 'Adresa nije uneta' }} · {{ $store->city ?: 'Mesto nije uneto' }}</p>
                    </div>
                    @if ($store->is_active)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktivna</span>
                    @else
                        <span class="rounded-full !bg-red-600 px-2.5 py-1 text-xs font-semibold !text-white">Deaktivirana</span>
                    @endif
                </div>
                <dl class="mt-5 grid grid-cols-3 gap-3 text-sm">
                    <div><dt class="text-slate-500">Prodavci</dt><dd class="mt-1 text-lg font-semibold">{{ $store->users_count }}</dd></div>
                    <div><dt class="text-slate-500">Dužnici</dt><dd class="mt-1 text-lg font-semibold">{{ $store->debtors_count }}</dd></div>
                    <div><dt class="text-slate-500">Zaduženja</dt><dd class="mt-1 text-lg font-semibold">{{ $store->debts_count }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('admin.stores.toggle', $store) }}" class="mt-5">
                    @csrf
                    <button class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">{{ $store->is_active ? 'Deaktiviraj optiku' : 'Aktiviraj optiku' }}</button>
                </form>
            </article>
        @empty
            <div class="rounded-xl bg-white p-8 text-center text-slate-500 shadow-sm ring-1 ring-slate-200">Nema kreiranih optika.</div>
        @endforelse
    </div>
@endsection

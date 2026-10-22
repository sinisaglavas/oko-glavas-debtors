@extends('layouts.app')

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Početna</p>
        <h1 class="mt-2 text-2xl font-bold">Dobro došli, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-slate-600">
            Prijavljeni ste kao {{ auth()->user()->isAdmin() ? 'administrator' : 'prodavac' }}.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('vendor.dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">Rad sa dužnicima</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Administracija</a>
            @endif
        </div>
    </div>
@endsection

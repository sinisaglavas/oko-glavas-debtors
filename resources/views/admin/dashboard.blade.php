@extends('layouts.app')

@php use App\Support\Money; @endphp

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Administracija</p>
            <h1 class="mt-2 text-2xl font-bold">Admin panel</h1>
            <p class="mt-1 text-slate-600">Kontrola korisnika, finansijskih podataka i aktivnosti sistema.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Korisnici</a>
            <a href="{{ route('admin.debtors.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Dužnici</a>
            <a href="{{ route('admin.audit') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Audit log</a>
        </div>
    </div>

    <form method="GET" class="mt-6 flex max-w-sm items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="flex-1"><label for="year" class="block text-sm font-medium">Godina</label><select id="year" name="year" class="mt-1 block w-full rounded-lg border-slate-300">@for ($option = now()->year; $option >= now()->year - 5; $option--)<option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>@endfor</select></div>
        <button class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Prikaži</button>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([['Početni dug', $originalTotal, 'text-slate-900'], ['Uplaćeno', $paidTotal, 'text-emerald-700'], ['Preostalo', $outstandingTotal, 'text-amber-700']] as [$label, $amount, $color])
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">{{ $label }} · {{ $year }}</p><p class="mt-2 text-2xl font-bold {{ $color }}">{{ Money::formatMinor($amount) }} RSD</p></div>
        @endforeach
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Dužnici</p><p class="mt-2 text-2xl font-bold">{{ $activeDebtors + $settledDebtors }}</p><p class="mt-1 text-xs text-slate-500">{{ $activeDebtors }} aktivnih · {{ $settledDebtors }} izmirenih</p></div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between"><h2 class="font-bold">Poslednje uplate</h2><a href="{{ route('debtors.index', ['year' => $year]) }}" class="text-sm font-semibold text-indigo-700">Svi dužnici</a></div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($recentPayments as $payment)
                    <div class="flex justify-between gap-3 py-3 text-sm"><div><p class="font-semibold">{{ $payment->debt->debtor->full_name }}</p><p class="text-slate-500">{{ $payment->paid_on->format('d.m.Y.') }} · {{ $payment->creator?->name ?: '—' }}</p></div><span class="font-semibold text-emerald-700">{{ Money::formatMinor($payment->amount_minor) }} RSD</span></div>
                @empty <p class="py-3 text-sm text-slate-500">Nema uplata.</p> @endforelse
            </div>
        </section>
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between"><h2 class="font-bold">Aktivnosti sistema</h2><a href="{{ route('admin.audit') }}" class="text-sm font-semibold text-indigo-700">Otvori audit log</a></div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($recentLogs as $log)
                    <div class="py-3 text-sm"><p class="font-semibold">{{ $log->action }}</p><p class="text-slate-500">{{ $log->user?->name ?: 'Sistem' }} · {{ $log->created_at->format('d.m.Y. H:i') }}</p></div>
                @empty <p class="py-3 text-sm text-slate-500">Nema zabeleženih aktivnosti.</p> @endforelse
            </div>
        </section>
    </div>
@endsection

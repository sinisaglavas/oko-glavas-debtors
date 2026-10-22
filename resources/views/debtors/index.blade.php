@extends('layouts.app')

@php
    use App\Support\Money;
@endphp

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Pregled</p>
            <h1 class="mt-2 text-2xl font-bold">Dužnici po godini</h1>
            <p class="mt-1 text-slate-600">Pregled trenutnog stanja zaduženja nastalih u izabranoj godini.</p>
        </div>
        <a href="{{ route('debtors.create') }}" class="inline-flex justify-center rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Novi dužnik / zaduženje</a>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('debtors.index') }}" class="mt-6 grid gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div>
            <label for="year" class="block text-sm font-medium">Godina</label>
            <select id="year" name="year" class="mt-1 block w-full rounded-lg border-slate-300">
                <option value="{{ now()->year }}" @selected($year === now()->year)>{{ now()->year }}</option>
                @foreach ($years as $availableYear)
                    @if ($availableYear !== now()->year)
                        <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="search" class="block text-sm font-medium">Pretraga</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Ime, prezime ili telefon" class="mt-1 block w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300">
                <option value="">Svi dužnici</option>
                <option value="active" @selected($status === 'active')>Aktivni</option>
                <option value="settled" @selected($status === 'settled')>Izmireni</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button class="flex-1 rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Primeni</button>
            <a href="{{ route('debtors.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([['Početni dug', $originalTotal, 'text-slate-900'], ['Uplaćeno', $paidTotal, 'text-emerald-700'], ['Preostali dug', $outstandingTotal, 'text-amber-700']] as [$label, $amount, $color])
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm text-slate-500">{{ $label }} · {{ $year }}</p>
                <p class="mt-2 text-2xl font-bold {{ $color }}">{{ Money::formatMinor($amount) }} RSD</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Dužnik</th>
                        <th class="px-5 py-3">Mesto</th>
                        <th class="px-5 py-3">Telefon</th>
                        <th class="px-5 py-3 text-right">Početni dug</th>
                        <th class="px-5 py-3 text-right">Uplaćeno</th>
                        <th class="px-5 py-3 text-right">Preostalo</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($debtors as $debtor)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-5 py-4 font-semibold">{{ $debtor->full_name }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $debtor->city }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $debtor->phone }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">{{ Money::formatMinor($debtor->annual_original_minor) }} RSD</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-emerald-700">{{ Money::formatMinor($debtor->annual_paid_minor) }} RSD</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-amber-700">{{ Money::formatMinor($debtor->annual_outstanding_minor) }} RSD</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($debtor->annual_status === 'settled')
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Izmiren</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Aktivan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">Nema dužnika za izabrane kriterijume.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($debtors->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $debtors->links() }}</div>
        @endif
    </div>
@endsection

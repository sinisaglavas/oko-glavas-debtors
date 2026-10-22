@extends('layouts.app')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('debtors.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Nazad na pregled</a>
        <h1 class="mt-3 text-2xl font-bold">Novi dužnik / zaduženje</h1>
        <p class="mt-1 text-slate-600">Izaberite postojeću osobu ili unesite podatke za novog dužnika, zatim dodajte zaduženje.</p>

        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-semibold">Proverite označena polja.</p>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('debtors.store') }}" class="mt-6 space-y-6">
            @csrf
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold">Dužnik</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="debtor_id" class="block text-sm font-medium">Postojeći dužnik (opciono)</label>
                        <select id="debtor_id" name="debtor_id" class="mt-1 block w-full rounded-lg border-slate-300">
                            <option value="">Novi dužnik</option>
                            @foreach ($debtors as $debtor)
                                <option value="{{ $debtor->id }}" @selected(old('debtor_id') == $debtor->id)>{{ $debtor->full_name }} · {{ $debtor->phone }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Ako izaberete postojeću osobu, podaci ispod se ne menjaju.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label for="first_name" class="block text-sm font-medium">Ime</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                        <div><label for="last_name" class="block text-sm font-medium">Prezime</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                        <div class="sm:col-span-2"><label for="address" class="block text-sm font-medium">Adresa</label><input id="address" name="address" value="{{ old('address') }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                        <div><label for="city" class="block text-sm font-medium">Mesto</label><input id="city" name="city" value="{{ old('city') }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                        <div><label for="phone" class="block text-sm font-medium">Telefon</label><input id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold">Zaduženje</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><label for="incurred_on" class="block text-sm font-medium">Datum zaduženja</label><input id="incurred_on" name="incurred_on" type="date" value="{{ old('incurred_on', now()->toDateString()) }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                    <div><label for="amount" class="block text-sm font-medium">Ukupan dug (RSD)</label><input id="amount" name="amount" inputmode="decimal" placeholder="24000,00" value="{{ old('amount') }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                    <div><label for="installment_count" class="block text-sm font-medium">Broj rata (2–24)</label><input id="installment_count" name="installment_count" type="number" min="2" max="24" value="{{ old('installment_count', 12) }}" class="mt-1 block w-full rounded-lg border-slate-300"></div>
                </div>
            </section>

            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">Sačuvaj dužnika i zaduženje</button>
        </form>
    </div>
@endsection

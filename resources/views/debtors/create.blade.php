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
                <h2 class="text-lg font-semibold">Klijent</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="debtor_id" class="block text-sm font-medium">Postojeći dužnik (opciono)</label>
                        <select id="debtor_id" name="debtor_id" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3 text-base">
                            <option value="">Novi dužnik</option>
                            @foreach ($debtors as $debtor)
                                <option value="{{ $debtor->id }}" @selected(old('debtor_id') == $debtor->id)>{{ $debtor->full_name }} · {{ $debtor->phone }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Ako izaberete postojeću osobu, podaci ispod se ne menjaju.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium">Ime <span class="text-red-600">*</span></label>
                            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="mt-1 block w-full rounded-lg border px-4 py-3 text-base @error('first_name') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror">
                            @error('first_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium">Prezime <span class="text-red-600">*</span></label>
                            <input id="last_name" name="last_name" value="{{ old('last_name') }}" required class="mt-1 block w-full rounded-lg border px-4 py-3 text-base @error('last_name') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror">
                            @error('last_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2"><label for="address" class="block text-sm font-medium">Adresa <span class="font-normal text-slate-500">(opciono)</span></label><input id="address" name="address" value="{{ old('address') }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3 text-base"></div>
                        <div><label for="city" class="block text-sm font-medium">Mesto <span class="font-normal text-slate-500">(opciono)</span></label><input id="city" name="city" value="{{ old('city') }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3 text-base"></div>
                        <div>
                            <label for="phone" class="block text-sm font-medium">Telefon <span class="text-red-600">*</span></label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" required class="mt-1 block w-full rounded-lg border px-4 py-3 text-base @error('phone') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror">
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-semibold">Zaduženje</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><label for="incurred_on" class="block text-sm font-medium">Datum zaduženja</label><input id="incurred_on" name="incurred_on" type="date" value="{{ old('incurred_on', now()->toDateString()) }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3 text-base"></div>
                    <div>
                        <label for="amount" class="block text-sm font-medium">Ukupan dug (RSD) <span class="text-red-600">*</span></label>
                        <input id="amount" name="amount" inputmode="decimal" placeholder="24000,00" value="{{ old('amount') }}" required class="mt-1 block w-full rounded-lg border px-4 py-3 text-base @error('amount') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror">
                        @error('amount')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div><label for="installment_count" class="block text-sm font-medium">Broj rata (2–24)</label><select id="installment_count" name="installment_count" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3 text-base">@for ($installments = 2; $installments <= 24; $installments++)<option value="{{ $installments }}" @selected((int) old('installment_count', 12) === $installments)>{{ $installments }}</option>@endfor</select></div>
                </div>
            </section>

            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Sačuvaj dužnika i zaduženje</button>
        </form>
    </div>
@endsection

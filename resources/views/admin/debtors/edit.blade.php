@extends('layouts.app')

@section('content')
    <div class="max-w-2xl"><a href="{{ route('admin.debtors.index') }}" class="text-sm font-semibold text-indigo-700">← Dužnici</a><h1 class="mt-3 text-2xl font-bold">Izmena podataka dužnika</h1><p class="mt-1 text-slate-600">Finansijska zaduženja i uplate ostaju nepromenjeni.</p>
        @if ($errors->any())<div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"><ul class="list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('admin.debtors.update', $debtor) }}" class="mt-6 space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2"><div><label for="first_name" class="block text-sm font-medium">Ime</label><input id="first_name" name="first_name" value="{{ old('first_name', $debtor->first_name) }}" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div><div><label for="last_name" class="block text-sm font-medium">Prezime</label><input id="last_name" name="last_name" value="{{ old('last_name', $debtor->last_name) }}" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div></div>
            <div><label for="address" class="block text-sm font-medium">Adresa</label><input id="address" name="address" value="{{ old('address', $debtor->address) }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label for="city" class="block text-sm font-medium">Mesto</label><input id="city" name="city" value="{{ old('city', $debtor->city) }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div><div><label for="phone" class="block text-sm font-medium">Telefon</label><input id="phone" name="phone" value="{{ old('phone', $debtor->phone) }}" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div></div>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Sačuvaj izmene</button>
        </form>
    </div>
@endsection

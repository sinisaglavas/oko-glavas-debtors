@extends('layouts.app')

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Prodavac</p>
        <h1 class="mt-2 text-2xl font-bold">Rad sa dužnicima</h1>
        <p class="mt-2 text-slate-600">Unesite dužnika i pratite zaduženja. Izmena podataka i storno uplata biće dostupni samo administratoru.</p>
        <a href="{{ route('debtors.index') }}" class="mt-6 inline-flex rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">Otvori godišnji pregled</a>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.stores.index') }}" class="text-sm font-semibold text-indigo-700">← Optike</a>
        <h1 class="mt-3 text-2xl font-bold">Nova optika</h1>
        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"><ul class="list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('admin.stores.store') }}" class="mt-6 space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            @csrf
            <div><label for="name" class="block text-sm font-medium">Naziv optike</label><input id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div>
            <div><label for="code" class="block text-sm font-medium">Interna oznaka</label><input id="code" name="code" value="{{ old('code') }}" placeholder="NS-2" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label for="address" class="block text-sm font-medium">Adresa <span class="font-normal text-slate-500">(opciono)</span></label><input id="address" name="address" value="{{ old('address') }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div><div><label for="city" class="block text-sm font-medium">Mesto <span class="font-normal text-slate-500">(opciono)</span></label><input id="city" name="city" value="{{ old('city') }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div></div>
            <div><label for="phone" class="block text-sm font-medium">Telefon <span class="font-normal text-slate-500">(opciono)</span></label><input id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3"></div>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Sačuvaj optiku</button>
        </form>
    </div>
@endsection

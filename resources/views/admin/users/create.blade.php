@extends('layouts.app')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-indigo-700">← Korisnici</a>
        <h1 class="mt-3 text-2xl font-bold">Novi korisnik</h1>
        <p class="mt-1 text-slate-600">Kreirajte nalog za administratora ili prodavca.</p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium">Ime i prezime</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-medium">Lozinka</label>
                    <input id="password" type="password" name="password" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3">
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium">Potvrda lozinke</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3">
                    @error('password_confirmation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="is_admin" class="block text-sm font-medium">Uloga</label>
                <select id="is_admin" name="is_admin" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3">
                    <option value="0">Prodavac</option>
                    <option value="1" @selected(old('is_admin') === '1')>Administrator</option>
                </select>
                @error('is_admin')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="store_id" class="block text-sm font-medium">Optika prodavca</label>
                <select id="store_id" name="store_id" class="mt-1 block w-full rounded-lg border-slate-300 px-4 py-3">
                    <option value="">Izaberite optiku</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}" @selected((string) old('store_id') === (string) $store->id)>{{ $store->name }} ({{ $store->code }})</option>
                    @endforeach
                </select>
                @error('store_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">Obavezno za prodavca; administrator ima pristup svim optikama.</p>
            </div>
            <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Kreiraj korisnika</button>
        </form>
    </div>
@endsection

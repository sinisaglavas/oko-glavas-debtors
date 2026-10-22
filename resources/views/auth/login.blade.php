<!DOCTYPE html>
<html lang="sr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Prijava | Evidencija dužnika</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 px-4 py-12 text-slate-900">
        <main class="mx-auto max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Optika</p>
            <h1 class="mt-2 text-2xl font-bold">Evidencija dužnika</h1>
            <p class="mt-2 text-sm text-slate-600">Prijavite se da biste nastavili.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium">Email adresa</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium">Lozinka</label>
                    <input id="password" name="password" type="password" required
                        class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600">
                    Zapamti me
                </label>

                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">
                    Prijavi se
                </button>
            </form>
        </main>
    </body>
</html>

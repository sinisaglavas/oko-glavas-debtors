<!DOCTYPE html>
<html lang="sr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Evidencija dužnika' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('dashboard') }}" class="font-bold">Evidencija dužnika</a>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-slate-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="font-semibold text-indigo-600 hover:text-indigo-800">Odjava</button>
                    </form>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            @yield('content')
        </main>
    </body>
</html>

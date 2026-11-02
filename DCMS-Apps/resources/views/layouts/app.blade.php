<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DCMS Repeat Order')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 bg-slate-950 text-white md:block">
            <div class="border-b border-white/10 px-6 py-5">
                <div class="text-lg font-bold">DCMS</div>
                <div class="text-xs text-slate-400">Repeat Order Intelligence</div>
            </div>
            <nav class="space-y-1 px-3 py-5 text-sm">
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'bg-white/10' : '' }}">Dashboard</a>
                <a href="{{ route('imports.index') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10 {{ request()->routeIs('imports.*') ? 'bg-white/10' : '' }}">Import Cross-selling</a>
                <a href="{{ route('predictions.index') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10 {{ request()->routeIs('predictions.*') ? 'bg-white/10' : '' }}">Pelanggan Potensial</a>
                <a href="{{ route('broadcasts.index') }}" class="block rounded-lg px-3 py-2 hover:bg-white/10 {{ request()->routeIs('broadcasts.*') ? 'bg-white/10' : '' }}">Laporan Broadcast</a>
            </nav>
        </aside>
        <main class="min-w-0 flex-1">
            <header class="border-b border-slate-200 bg-white px-4 py-3 md:px-8">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-700">@yield('title')</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold hover:bg-slate-50">Keluar</button>
                    </form>
                </div>
            </header>
            <div class="p-4 md:p-8">
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

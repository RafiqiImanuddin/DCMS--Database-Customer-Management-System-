<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - DCMS Gadgetmart</title>
    @yield('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-800 bg-slate-950 text-white shadow-2xl transition-transform duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0">
            <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
                <div class="min-w-0">
                    <div class="text-lg font-black tracking-tight">DCMS</div>
                    <div class="text-xs font-medium text-slate-400">Database Customer Management System</div>
                </div>
                <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="ml-auto h-14 w-14 shrink-0 rounded-lg bg-white object-contain p-1 shadow-sm">
            </div>
            <nav class="space-y-1.5 px-4 py-6 text-sm">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/></svg>Dashboard</a>
                @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('imports.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('imports.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg>Import Cross-selling</a>
                @endif
                <a href="{{ route('predictions.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('predictions.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 2v6m3-3h-6"/></svg>Master Customer</a>
                <a href="{{ route('leads.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('leads.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>Leads Customer</a>
                <a href="{{ route('broadcasts.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('broadcasts.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>Laporan Broadcast</a>
                <a href="{{ route('whatsapp.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('whatsapp.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                    <span>WhatsApp CRM</span>
                    <span class="ml-auto rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-300">Tim</span>
                </a>
                @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('settings.watzap.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('settings.watzap.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.3a2 2 0 0 1 3.4 0l.9 1.6a2 2 0 0 0 1.5.9l1.8.3a2 2 0 0 1 1.7 1.7l.3 1.8a2 2 0 0 0 .9 1.5l1.6.9a2 2 0 0 1 0 3.4l-1.6.9a2 2 0 0 0-.9 1.5l-.3 1.8a2 2 0 0 1-1.7 1.7l-1.8.3a2 2 0 0 0-1.5.9l-.9 1.6a2 2 0 0 1-3.4 0l-.9-1.6a2 2 0 0 0-1.5-.9l-1.8-.3a2 2 0 0 1-1.7-1.7l-.3-1.8a2 2 0 0 0-.9-1.5l-1.6-.9a2 2 0 0 1 0-3.4l1.6-.9a2 2 0 0 0 .9-1.5l.3-1.8a2 2 0 0 1 1.7-1.7l1.8-.3a2 2 0 0 0 1.5-.9l.9-1.6z"/><circle cx="12" cy="12" r="3"/></svg>
                    Pengaturan WatZap
                </a>
                @endif
            </nav>
            <div class="absolute inset-x-4 bottom-5 rounded-xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-center justify-between"><span class="text-xs text-slate-400">Login sebagai</span><span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ auth()->user()->isSuperAdmin() ? 'text-amber-300' : 'text-emerald-300' }}">{{ auth()->user()->isSuperAdmin() ? 'Superadmin' : 'Admin' }}</span></div>
                <div class="mt-1 truncate text-sm font-bold">{{ auth()->user()->name }}</div>
                <div class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</div>
            </div>
        </aside>
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm md:hidden"></div>
        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur md:px-8">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button id="sidebar-toggle" type="button" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 md:hidden" aria-label="Buka menu">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div><div class="text-sm font-bold text-slate-900">@yield('title')</div><div class="hidden text-xs text-slate-400 sm:block">DCMS Gadgetmart</div></div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m11 9h4a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3h-4"/></svg><span class="hidden sm:inline">Keluar</span></button>
                    </form>
                </div>
            </header>
            <div class="mx-auto max-w-[1600px] p-4 md:p-8">
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

    <!-- Floating WhatsApp Launcher (Multi-Agent, Persistent Anti-Logout) -->
    <div id="floating-whatsapp-container" class="fixed bottom-5 right-5 z-40">
        <button id="open-whatsapp-drawer-btn" type="button" onclick="toggleWhatsAppDrawer()" class="group inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-xl shadow-emerald-950/25 transition-all hover:bg-emerald-500 hover:scale-105 active:scale-95">
            <svg class="size-4 text-white animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
            <span class="font-bold">WhatsApp Tim</span>
            <span class="size-2 rounded-full bg-emerald-300"></span>
        </button>
    </div>

    <!-- WhatsApp Slide-over In-App Drawer (Anti-Logout saat gonta-ganti menu) -->
    <div id="whatsapp-drawer-backdrop" onclick="toggleWhatsAppDrawer()" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-xs transition-opacity duration-300"></div>
    <div id="whatsapp-drawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-4xl translate-x-full bg-white shadow-2xl transition-transform duration-300 flex flex-col">
        <div class="h-12 border-b border-slate-200 bg-slate-900 px-4 flex items-center justify-between text-white shrink-0">
            <div class="flex items-center gap-2">
                <svg class="size-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                <span class="text-xs font-black tracking-wide">WhatsApp Tim & CRM (Anti-Logout)</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('whatsapp.index') }}" class="inline-flex items-center gap-1 rounded bg-white/10 px-2 py-1 text-[11px] font-semibold text-slate-200 hover:bg-white/20 transition">
                    <span>↗</span> Buka Layar Penuh
                </a>
                <button type="button" onclick="toggleWhatsAppDrawer()" class="text-slate-400 hover:text-white text-lg font-bold p-1 leading-none">&times;</button>
            </div>
        </div>
        <div class="flex-1 relative bg-slate-100">
            <iframe id="whatsapp-drawer-iframe" src="about:blank" data-src="{{ route('whatsapp.index') }}" class="w-full h-full border-0"></iframe>
        </div>
    </div>

    <script>
        function toggleWhatsAppDrawer() {
            const drawer = document.getElementById('whatsapp-drawer');
            const backdrop = document.getElementById('whatsapp-drawer-backdrop');
            const iframe = document.getElementById('whatsapp-drawer-iframe');

            if (drawer.classList.contains('translate-x-full')) {
                // Buka panel samping
                drawer.classList.remove('translate-x-full');
                backdrop.classList.remove('hidden');
                if (iframe.src === 'about:blank' || !iframe.src || iframe.src.endsWith('about:blank')) {
                    iframe.src = iframe.getAttribute('data-src');
                }
            } else {
                // Tutup panel samping tanpa logout
                drawer.classList.add('translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
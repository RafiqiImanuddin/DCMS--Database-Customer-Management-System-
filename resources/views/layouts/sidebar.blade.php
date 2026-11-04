<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex flex-col w-72 -translate-x-full border-r border-slate-800 bg-slate-950 text-white shadow-2xl transition-transform duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0">
    <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6 shrink-0">
        <div class="min-w-0">
            <div class="text-lg font-black tracking-tight">DCMS</div>
            <div class="text-xs font-medium text-slate-400">Database Customer Management System</div>
        </div>
        <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="ml-auto h-14 w-14 shrink-0 rounded-lg bg-white object-contain p-1 shadow-sm">
    </div>

    <nav class="flex-1 overflow-y-auto space-y-1 px-4 py-4 text-sm">
        <!-- GROUP 1: CUSTOMER & OUTREACH CRM -->
        <div class="px-3 pb-1 pt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Customer & Outreach</div>
        
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/></svg>
            <span>Dashboard</span>
        </a>

        @if (auth()->check() && auth()->user()->isSuperAdmin())
        <a href="{{ route('imports.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ request()->routeIs('imports.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg>
            <span>Import Cross-selling</span>
        </a>
        @endif

        <a href="{{ route('predictions.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ request()->routeIs('predictions.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 2v6m3-3h-6"/></svg>
            <span>Master Customer</span>
        </a>

        <a href="{{ route('leads.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ request()->routeIs('leads.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <span>Leads Customer</span>
        </a>

        <a href="{{ route('broadcasts.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ request()->routeIs('broadcasts.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>
            <span>Laporan Broadcast</span>
        </a>

        <a href="{{ route('settings.watzap.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ request()->routeIs('settings.watzap.*', 'whatsapp.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
            <span>Setup WhatsApp</span>
        </a>

        <!-- GROUP 2: WORKSPACE SOSMED (INTEGRASI PENUH) -->
        <div class="pt-4 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-purple-400 flex items-center justify-between">
            <span>Workspace Sosmed</span>
            <span class="rounded bg-purple-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-purple-300">LIVE</span>
        </div>

        <!-- 1. Dashboard KPI -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 1]) }}" data-tab="1" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(1);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab', 1) == 1) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            <span>Dashboard Sosmed</span>
        </a>

        <!-- 2. Report Matrix -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 2]) }}" data-tab="2" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(2);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab') == 2) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625a1.125 1.125 0 0 0 1.125-1.125m-1.125 1.125h-7.5a1.125 1.125 0 0 1-1.125-1.125m8.625 0V5.625m0 12.75v-1.5c0-.621-.504-1.125-1.125-1.125m0 0h-7.5m7.5 0V3.375m0 19.5a1.125 1.125 0 0 1-1.125 1.125"/></svg>
            <span>Report Matrix</span>
        </a>

        <!-- 3. Cek Status AR -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 3]) }}" data-tab="3" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(3);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab') == 3) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <span>Cek Status AR</span>
        </a>

        <!-- 4. Format Keep -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 4]) }}" data-tab="4" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(4);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab') == 4) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            <span>Format Keep</span>
        </a>

        <!-- 5. Tracking AR -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 5]) }}" data-tab="5" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(5);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab') == 5) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
            <span>Tracking AR</span>
        </a>

        <!-- 6. Klaim AR -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 6]) }}" data-tab="6" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(6);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab') == 6) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>Klaim AR</span>
        </a>

        <!-- 7. Inbox FU -->
        <a href="{{ route('workspace-sosmed.index', ['tab' => 9]) }}" data-tab="9" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(9);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2 font-medium transition {{ (request()->routeIs('workspace-sosmed.*') && request()->query('tab') == 9) ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
            <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.125-.68.39-1.53.844-2.228C4.015 16.544 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
            <span>Inbox Follow-Up</span>
            <span class="ml-auto rounded-full bg-emerald-500/20 px-1.5 py-0.5 text-[9px] font-bold text-emerald-300">FU</span>
        </a>
    </nav>

    <div class="shrink-0 p-4 border-t border-white/10 bg-white/5">
        <div class="flex items-center justify-between"><span class="text-xs text-slate-400">Login sebagai</span><span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ auth()->check() && auth()->user()->isSuperAdmin() ? 'text-amber-300' : 'text-emerald-300' }}">{{ auth()->check() && auth()->user()->isSuperAdmin() ? 'Superadmin' : 'Admin' }}</span></div>
        <div class="mt-1 truncate text-sm font-bold">{{ auth()->user()->name ?? 'User' }}</div>
        <div class="truncate text-xs text-slate-400">{{ auth()->user()->email ?? '' }}</div>
    </div>
</aside>


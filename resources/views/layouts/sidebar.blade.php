<style>
#sidebar, #sidebar *, #sidebar a, #sidebar span, #sidebar div, #sidebar p {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    box-sizing: border-box !important;
}
#sidebar {
    width: 18rem !important;
    min-width: 18rem !important;
    max-width: 18rem !important;
    flex-shrink: 0 !important;
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease !important;
}

@media (min-width: 768px) {
    #sidebar {
        margin-left: 0;
    }
    html.sidebar-collapsed #sidebar,
    body.sidebar-collapsed #sidebar {
        margin-left: -18rem !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
}
.sidebar-group-content {
    overflow: hidden;
    transition: max-height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease-in-out;
}
.sidebar-group-content.is-collapsed {
    max-height: 0 !important;
    opacity: 0 !important;
    pointer-events: none !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
.sidebar-group-content.is-expanded {
    max-height: 800px;
    opacity: 1;
}
.sidebar-group-chevron {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-group-chevron.is-collapsed {
    transform: rotate(-90deg);
}
.sidebar-group-chevron.is-expanded {
    transform: rotate(0deg);
}
</style>

<aside id="sidebar" style="font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; width: 18rem !important; min-width: 18rem !important; max-width: 18rem !important; flex-shrink: 0 !important;" class="fixed inset-y-0 left-0 z-40 flex flex-col w-72 shrink-0 -translate-x-full border-r border-slate-800 bg-slate-950 text-white shadow-2xl transition-transform duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0">
    <div class="flex h-20 items-center gap-2.5 border-b border-white/10 px-5 shrink-0">
        <div class="min-w-0 flex-1">
            <div class="text-lg font-black tracking-tight leading-tight">DCMS</div>
            <div class="text-[11px] font-medium text-slate-400 leading-snug truncate">Database Customer Management</div>
        </div>
        <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="h-10 w-10 shrink-0 rounded-lg bg-white object-contain p-1 shadow-sm">
        <button type="button" 
                onclick="if(window.toggleDcmsSidebar){window.toggleDcmsSidebar(false)}" 
                class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white transition cursor-pointer" 
                title="Sembunyikan Sidebar (Ctrl+B)" 
                aria-label="Sembunyikan Sidebar">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto space-y-2 px-4 py-3 text-sm">
        @php
            $activeTarget = \App\Support\RouteCipher::resolveTarget(request());
            $activeWorkspaceTab = \App\Support\RouteCipher::resolveWorkspaceTab(request());

            $isCustomerActive = in_array($activeTarget, [
                'dashboard', 'imports.index', 'predictions.index', 'leads.index', 'broadcasts.index'
            ]);
            $isWorkspaceActive = ($activeTarget === 'workspace-sosmed.index');
            $isMonitoringActive = in_array($activeTarget, ['monitoring.sales.index']);
            $isGuidanceActive = ($activeTarget === 'guidance.index');
            $isSettingActive = in_array($activeTarget, ['settings.watzap.index', 'whatsapp.index', 'settings.karyawan.index']);
        @endphp

        <!-- GROUP 1: CUSTOMER & OUTREACH CRM -->
        <div class="pt-1">
            <button type="button" 
                    id="btn-sidebar-group-customer"
                    onclick="toggleSidebarGroup('customer')" 
                    class="group flex w-full items-center justify-between px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-lg transition-colors select-none cursor-pointer focus:outline-none"
                    aria-expanded="true"
                    aria-controls="sidebar-group-customer"
                    title="Klik untuk collapse / expand">
                <span class="flex items-center gap-1.5">
                    <span>Customer & Outreach</span>
                    @if ($isCustomerActive)
                        <span id="dot-active-customer" class="size-1.5 rounded-full bg-brand-400 ring-2 ring-brand-400/20" style="display: none;" title="Menu aktif di grup ini"></span>
                    @endif
                </span>
                <svg id="chevron-customer" class="sidebar-group-chevron size-3 text-slate-500 group-hover:text-slate-300 is-expanded" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            
            <div id="sidebar-group-customer" class="sidebar-group-content space-y-1 pt-1 is-expanded">
                <a href="{{ \App\Support\RouteCipher::url('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'dashboard' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/></svg>
                    <span>Dashboard</span>
                </a>

                @if (auth()->check() && auth()->user()->isSuperAdmin())
                <a href="{{ \App\Support\RouteCipher::url('imports.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'imports.index' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg>
                    <span>Pusat Unggah Data</span>
                </a>
                @endif

                <a href="{{ \App\Support\RouteCipher::url('predictions.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'predictions.index' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 2v6m3-3h-6"/></svg>
                    <span>Master Customer</span>
                </a>

                <a href="{{ \App\Support\RouteCipher::url('leads.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'leads.index' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <span>Leads Customer</span>
                </a>

                <a href="{{ \App\Support\RouteCipher::url('broadcasts.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'broadcasts.index' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>
                    <span>Laporan Broadcast</span>
                </a>
            </div>
        </div>

        <!-- GROUP 2: WORKSPACE SOSMED (INTEGRASI PENUH) -->
        <div class="pt-2">
            <button type="button" 
                    id="btn-sidebar-group-workspace"
                    onclick="toggleSidebarGroup('workspace')" 
                    class="group flex w-full items-center justify-between px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-lg transition-colors select-none cursor-pointer focus:outline-none"
                    aria-expanded="true"
                    aria-controls="sidebar-group-workspace"
                    title="Klik untuk collapse / expand">
                <span class="flex items-center gap-1.5">
                    <span>Workspace Sosmed</span>
                    @if ($isWorkspaceActive)
                        <span id="dot-active-workspace" class="size-1.5 rounded-full bg-brand-400 ring-2 ring-brand-400/20" style="display: none;" title="Menu aktif di grup ini"></span>
                    @endif
                </span>
                <svg id="chevron-workspace" class="sidebar-group-chevron size-3 text-slate-500 group-hover:text-slate-300 is-expanded" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            <div id="sidebar-group-workspace" class="sidebar-group-content space-y-1 pt-1 is-expanded">
                <!-- 1. Dashboard KPI -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 1]) }}" data-tab="1" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(1);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 1) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    <span>Dashboard Sosmed</span>
                </a>

                <!-- 2. Report Matrix -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 2]) }}" data-tab="2" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(2);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 2) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625a1.125 1.125 0 0 0 1.125-1.125m-1.125 1.125h-7.5a1.125 1.125 0 0 1-1.125-1.125m8.625 0V5.625m0 12.75v-1.5c0-.621-.504-1.125-1.125-1.125m0 0h-7.5m7.5 0V3.375m0 19.5a1.125 1.125 0 0 1-1.125 1.125"/></svg>
                    <span>Report Matrix</span>
                </a>

                <!-- 3. Cek Status AR -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 3]) }}" data-tab="3" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(3);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 3) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <span>Cek Status AR</span>
                </a>

                <!-- 4. Format Keep -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 4]) }}" data-tab="4" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(4);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 4) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    <span>Format Keep</span>
                </a>

                <!-- 5. Tracking AR -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 5]) }}" data-tab="5" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(5);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 5) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                    <span>Tracking AR</span>
                </a>

                <!-- 6. Klaim AR -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 6]) }}" data-tab="6" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(6);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 6) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>Klaim AR</span>
                </a>

                <!-- 7. Inbox FU -->
                <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 9]) }}" data-tab="9" onclick="if(window.switchWorkspaceTab){window.switchWorkspaceTab(9);return false;}" class="workspace-sidebar-link flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ ($activeTarget === 'workspace-sosmed.index' && $activeWorkspaceTab === 9) ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.125-.68.39-1.53.844-2.228C4.015 16.544 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    <span>Inbox Follow-Up</span>
                    <span class="ml-auto rounded-full bg-emerald-500/20 px-1.5 py-0.5 text-[9px] font-bold text-emerald-300">FU</span>
                </a>
            </div>
        </div>

        <!-- GROUP 3: MONITORING (MANAGER & SUPERADMIN) -->
        <div class="pt-2">
            <button type="button" 
                    id="btn-sidebar-group-monitoring"
                    onclick="toggleSidebarGroup('monitoring')" 
                    class="group flex w-full items-center justify-between px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-lg transition-colors select-none cursor-pointer focus:outline-none"
                    aria-expanded="true"
                    aria-controls="sidebar-group-monitoring"
                    title="Klik untuk collapse / expand">
                <span class="flex items-center gap-1.5">
                    <span>Monitoring Kinerja</span>
                    <span class="rounded-full bg-indigo-500/20 px-1.5 py-0.2 text-[8px] font-bold text-indigo-300">Manager</span>
                    @if ($isMonitoringActive)
                        <span id="dot-active-monitoring" class="size-1.5 rounded-full bg-brand-400 ring-2 ring-brand-400/20" style="display: none;" title="Menu aktif di grup ini"></span>
                    @endif
                </span>
                <svg id="chevron-monitoring" class="sidebar-group-chevron size-3 text-slate-500 group-hover:text-slate-300 is-expanded" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            <div id="sidebar-group-monitoring" class="sidebar-group-content space-y-1 pt-1 is-expanded">
                <a href="{{ \App\Support\RouteCipher::url('monitoring.sales.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'monitoring.sales.index' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    <span>Monitoring Admin Sales</span>
                    <span class="ml-auto rounded-full bg-indigo-500/20 px-1.5 py-0.5 text-[9px] font-bold text-indigo-300">KPI</span>
                </a>
            </div>
        </div>

        <!-- GROUP 4: PANDUAN & SOP -->
        <div class="pt-2">
            <button type="button" 
                    id="btn-sidebar-group-guidance"
                    onclick="toggleSidebarGroup('guidance')" 
                    class="group flex w-full items-center justify-between px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-lg transition-colors select-none cursor-pointer focus:outline-none"
                    aria-expanded="true"
                    aria-controls="sidebar-group-guidance"
                    title="Klik untuk collapse / expand">
                <span class="flex items-center gap-1.5">
                    <span>Panduan & SOP</span>
                    @if (auth()->check() && auth()->user()->isSuperAdmin())
                        <span class="rounded-full bg-purple-500/20 px-1.5 py-0.2 text-[8px] font-bold text-purple-300">Rumus</span>
                    @else
                        <span class="rounded-full bg-sky-500/20 px-1.5 py-0.2 text-[8px] font-bold text-sky-300">SOP</span>
                    @endif
                    @if ($isGuidanceActive)
                        <span id="dot-active-guidance" class="size-1.5 rounded-full bg-brand-400 ring-2 ring-brand-400/20" style="display: none;" title="Menu aktif di grup ini"></span>
                    @endif
                </span>
                <svg id="chevron-guidance" class="sidebar-group-chevron size-3 text-slate-500 group-hover:text-slate-300 is-expanded" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            <div id="sidebar-group-guidance" class="sidebar-group-content space-y-1 pt-1 is-expanded">
                <a href="{{ \App\Support\RouteCipher::url('guidance.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'guidance.index' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0 text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                    </svg>
                    <span>{{ auth()->check() && auth()->user()->isSuperAdmin() ? 'Rumus & Panduan' : 'Panduan & SOP Sales' }}</span>
                    <span class="ml-auto rounded-full {{ auth()->check() && auth()->user()->isSuperAdmin() ? 'bg-purple-500/20 text-purple-300' : 'bg-sky-500/20 text-sky-300' }} px-1.5 py-0.5 text-[9px] font-bold">
                        {{ auth()->check() && auth()->user()->isSuperAdmin() ? 'Rumus' : 'SOP' }}
                    </span>
                </a>
            </div>
        </div>

        <!-- GROUP 5: SETTING -->
        <div class="pt-2">
            <button type="button" 
                    id="btn-sidebar-group-setting"
                    onclick="toggleSidebarGroup('setting')" 
                    class="group flex w-full items-center justify-between px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-lg transition-colors select-none cursor-pointer focus:outline-none"
                    aria-expanded="true"
                    aria-controls="sidebar-group-setting"
                    title="Klik untuk collapse / expand">
                <span class="flex items-center gap-1.5">
                    <span>Setting</span>
                    @if ($isSettingActive)
                        <span id="dot-active-setting" class="size-1.5 rounded-full bg-brand-400 ring-2 ring-brand-400/20" style="display: none;" title="Menu aktif di grup ini"></span>
                    @endif
                </span>
                <svg id="chevron-setting" class="sidebar-group-chevron size-3 text-slate-500 group-hover:text-slate-300 is-expanded" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            <div id="sidebar-group-setting" class="sidebar-group-content space-y-1 pt-1 is-expanded">
                <a href="{{ \App\Support\RouteCipher::url('settings.watzap.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ in_array($activeTarget, ['settings.watzap.index', 'whatsapp.index']) ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9c0 1.5.4 3 1.1 4.3L3 21l4.9-1.3A8.9 8.9 0 0 0 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a4 4 0 0 0 5 5"/></svg>
                    <span>Setup WhatsApp</span>
                    <span class="ml-auto rounded-full bg-emerald-500/20 px-1.5 py-0.5 text-[9px] font-bold text-emerald-300">Gateway</span>
                </a>

                <a href="{{ \App\Support\RouteCipher::url('settings.karyawan.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition {{ $activeTarget === 'settings.karyawan.index' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="size-5 shrink-0 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    <span>Karyawan & Bobot KPI</span>
                    <span class="ml-auto rounded-full bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-bold text-amber-300">Data</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="shrink-0 p-4 border-t border-white/10 bg-white/5">
        <div class="flex items-center justify-between"><span class="text-xs text-slate-400">Login sebagai</span><span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ auth()->check() && auth()->user()->isSuperAdmin() ? 'text-amber-300' : 'text-emerald-300' }}">{{ auth()->check() && auth()->user()->isSuperAdmin() ? 'Superadmin' : 'Admin' }}</span></div>
        <div class="mt-1 truncate text-sm font-bold">{{ auth()->user()->name ?? 'User' }}</div>
        <div class="truncate text-xs text-slate-400">{{ auth()->user()->email ?? '' }}</div>
    </div>
</aside>

<script>
window.toggleDcmsSidebar = function(forceOpen) {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebar-overlay');
    if (!sb) return;

    const isDesktop = window.innerWidth >= 768;

    if (isDesktop) {
        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed') ||
                            document.body.classList.contains('sidebar-collapsed');
        const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : isCollapsed;

        if (shouldOpen) {
            document.documentElement.classList.remove('sidebar-collapsed');
            document.body.classList.remove('sidebar-collapsed');
            try { localStorage.setItem('dcms_sidebar_collapsed', '0'); } catch (e) {}
        } else {
            document.documentElement.classList.add('sidebar-collapsed');
            document.body.classList.add('sidebar-collapsed');
            try { localStorage.setItem('dcms_sidebar_collapsed', '1'); } catch (e) {}
        }
    } else {
        const isClosed = sb.classList.contains('-translate-x-full');
        const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : isClosed;

        sb.classList.toggle('-translate-x-full', !shouldOpen);
        sb.classList.toggle('translate-x-0', shouldOpen);
        if (ov) ov.classList.toggle('hidden', !shouldOpen);
    }
};

(function initDesktopSidebarState() {
    try {
        if (localStorage.getItem('dcms_sidebar_collapsed') === '1' && window.innerWidth >= 768) {
            document.documentElement.classList.add('sidebar-collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
    } catch (e) {}
})();

window.toggleSidebarGroup = function(groupId) {
    const content = document.getElementById('sidebar-group-' + groupId);
    const chevron = document.getElementById('chevron-' + groupId);
    const btn = document.getElementById('btn-sidebar-group-' + groupId);
    const dot = document.getElementById('dot-active-' + groupId);
    if (!content) return;

    const isCurrentlyCollapsed = content.classList.contains('is-collapsed');
    const willExpand = isCurrentlyCollapsed;

    if (willExpand) {
        content.classList.remove('is-collapsed');
        content.classList.add('is-expanded');
        if (chevron) {
            chevron.classList.remove('is-collapsed');
            chevron.classList.add('is-expanded');
        }
        if (btn) btn.setAttribute('aria-expanded', 'true');
        if (dot) dot.style.display = 'none';
        try {
            localStorage.setItem('dcms_sidebar_' + groupId, 'expanded');
        } catch (e) {}
    } else {
        content.classList.remove('is-expanded');
        content.classList.add('is-collapsed');
        if (chevron) {
            chevron.classList.remove('is-expanded');
            chevron.classList.add('is-collapsed');
        }
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (dot) dot.style.display = 'inline-block';
        try {
            localStorage.setItem('dcms_sidebar_' + groupId, 'collapsed');
        } catch (e) {}
    }
};

(function initSidebarGroups() {
    const groups = [
        { id: 'customer', isActive: {{ $isCustomerActive ? 'true' : 'false' }} },
        { id: 'workspace', isActive: {{ $isWorkspaceActive ? 'true' : 'false' }} },
        { id: 'monitoring', isActive: {{ $isMonitoringActive ? 'true' : 'false' }} },
        { id: 'guidance', isActive: {{ $isGuidanceActive ? 'true' : 'false' }} },
        { id: 'setting', isActive: {{ $isSettingActive ? 'true' : 'false' }} }
    ];

    groups.forEach(function(g) {
        const content = document.getElementById('sidebar-group-' + g.id);
        const chevron = document.getElementById('chevron-' + g.id);
        const btn = document.getElementById('btn-sidebar-group-' + g.id);
        const dot = document.getElementById('dot-active-' + g.id);
        if (!content) return;

        let shouldBeExpanded = true;
        try {
            const saved = localStorage.getItem('dcms_sidebar_' + g.id);
            if (g.isActive) {
                // Selalu buka grup yang berisi halaman aktif saat ini
                shouldBeExpanded = true;
            } else if (saved !== null) {
                shouldBeExpanded = (saved === 'expanded');
            }
        } catch (e) {}

        if (shouldBeExpanded) {
            content.classList.remove('is-collapsed');
            content.classList.add('is-expanded');
            if (chevron) {
                chevron.classList.remove('is-collapsed');
                chevron.classList.add('is-expanded');
            }
            if (btn) btn.setAttribute('aria-expanded', 'true');
            if (dot) dot.style.display = 'none';
        } else {
            content.classList.remove('is-expanded');
            content.classList.add('is-collapsed');
            if (chevron) {
                chevron.classList.remove('is-expanded');
                chevron.classList.add('is-collapsed');
            }
            if (btn) btn.setAttribute('aria-expanded', 'false');
            if (dot) dot.style.display = 'inline-block';
        }
    });
})();
</script>

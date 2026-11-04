<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp CRM Inbox - GadgetMart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar for chat panels */
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden bg-slate-100 font-sans text-slate-800 antialiased select-none">

    <!-- Top Header Bar (matches screenshot) -->
    <header class="h-14 border-b border-slate-200 bg-white px-4 flex items-center justify-between shrink-0 z-30 shadow-xs">
        <div class="flex items-center gap-3">
            <!-- Brand Logo -->
            <a href="{{ route('whatsapp.index') }}" class="flex items-center gap-2 group">
                <img src="{{ asset('images/gadgetmart-logo.png') }}" class="h-8 w-auto object-contain transition group-hover:scale-105" alt="GadgetMart">
                <span class="text-lg font-black tracking-tight text-slate-900">Gadget<span class="text-blue-600">Mart</span></span>
            </a>

            <!-- Buka CRM Button -->
            <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-xs">
                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Buka CRM
            </a>


        </div>

        <div class="flex items-center gap-4">
            <!-- WatZap Gateway Status Indicator -->
            <div class="hidden lg:flex items-center gap-1.5 text-xs">
                @if($isConfigured)
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-emerald-700 font-semibold text-[11px]">WatZap Terhubung</span>
                @else
                    <span class="size-2 rounded-full bg-amber-500"></span>
                    <a href="{{ route('settings.watzap.index') }}" class="text-amber-700 underline font-semibold text-[11px]">Setup WatZap API</a>
                @endif
            </div>

            <!-- OFFLINE / ONLINE Toggle -->
            <div class="flex items-center gap-2">
                <span id="agent-status-label" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">ONLINE</span>
                <button type="button" id="toggle-status-btn" onclick="toggleStatus()" class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-emerald-500 transition-colors duration-200 ease-in-out focus:outline-none">
                    <span id="toggle-status-dot" class="translate-x-4 pointer-events-none inline-block size-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            </div>

            <!-- Role Badge -->
            <span class="rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[11px] font-bold text-slate-600 tracking-wider uppercase">
                {{ $currentUser->role ?? 'SUPERVISOR' }}
            </span>

            <!-- Profile Pill -->
            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                <div class="size-8 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs ring-1 ring-blue-300">
                    {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                </div>
                <div class="text-left hidden sm:block">
                    <div class="text-xs font-bold text-slate-800 leading-tight">{{ $currentUser->name }}</div>
                    <div class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $currentUser->email }}</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Workspace: 5 Columns -->
    <div class="h-[calc(100vh-56px)] flex overflow-hidden">

        <!-- ======================================================== -->
        <!-- COLUMN 1: Far Left Mini Icon Sidebar (56px)              -->
        <!-- ======================================================== -->
        <aside class="w-14 bg-white border-r border-slate-200 flex flex-col items-center py-3 justify-between shrink-0 z-20">
            <div class="flex flex-col items-center gap-3 w-full">
                <!-- Inbox / Active Chat Icon -->
                <a href="{{ route('whatsapp.index') }}" title="Inbox" class="p-2.5 rounded-xl bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </a>

                <!-- New Chat (+) Icon -->
                <button type="button" onclick="openNewChatModal()" title="Mulai Chat Baru" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>

                <!-- Quick Replies / Lightning Icon -->
                <button type="button" onclick="showQuickRepliesPalette()" title="Template Cepat (/)" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                </button>

                <!-- Filter / Funnel Icon -->
                <a href="{{ route('whatsapp.index', ['folder' => 'unresponded']) }}" title="Belum Direspons" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                </a>

                <!-- Broadcast / Palette Icon -->
                <a href="{{ route('broadcasts.index') }}" title="Broadcast Manager" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                </a>

                <!-- Contacts / Leads Icon -->
                <a href="{{ route('leads.index') }}" title="Leads Customer" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </a>

                <!-- Settings Icon -->
                <a href="{{ route('settings.watzap.index') }}" title="Pengaturan Gateway" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                </a>
            </div>

            <!-- CID text at bottom -->
            <div class="text-[9px] font-bold text-slate-400 rotate-180 [writing-mode:vertical-rl] tracking-widest pb-2">
                CID: 684518
            </div>
        </aside>

        <!-- ======================================================== -->
        <!-- COLUMN 2: Folder Navigator & WhatsApp Groups (230px)     -->
        <!-- ======================================================== -->
        <nav class="w-56 lg:w-60 bg-white border-r border-slate-200 flex flex-col shrink-0 custom-scroll overflow-y-auto">
            <!-- Header INBOX -->
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-black tracking-wider text-slate-500 uppercase">INBOX</span>
                <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <!-- Main Folders -->
            <div class="p-2 space-y-1 text-xs font-medium">
                <a href="{{ route('whatsapp.index', ['folder' => 'all']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ $folder === 'all' && empty($groupFilter) ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>All chats</span>
                    <span class="rounded-full {{ $folder === 'all' && empty($groupFilter) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 text-[11px] font-bold">{{ $folderCounts['all'] }}</span>
                </a>

                <a href="{{ route('whatsapp.index', ['folder' => 'my_chats']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ $folder === 'my_chats' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>My chats</span>
                    <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[11px] font-bold">{{ $folderCounts['my_chats'] }}</span>
                </a>

                <a href="{{ route('whatsapp.index', ['folder' => 'unassigned']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ $folder === 'unassigned' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Unassigned</span>
                    <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[11px] font-bold">{{ $folderCounts['unassigned'] }}</span>
                </a>

                <a href="{{ route('whatsapp.index', ['folder' => 'unresponded']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ $folder === 'unresponded' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Unresponded</span>
                    <span class="rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-bold">{{ $folderCounts['unresponded'] }}</span>
                </a>

                <a href="{{ route('whatsapp.index', ['folder' => 'resolved']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ $folder === 'resolved' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Resolved</span>
                    <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[11px] font-bold">{{ $folderCounts['resolved'] }}</span>
                </a>
            </div>

            <!-- WhatsApp Groups Section -->
            <div class="mt-2 border-t border-slate-100 p-2">
                <div class="px-3 py-1 flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">WHATSAPP GROUPS (902)</span>
                </div>

                <!-- Group Search Box -->
                <div class="px-2 py-1">
                    <div class="relative">
                        <input type="text" id="group-search" placeholder="Search group name..." class="w-full rounded-md border border-slate-200 bg-slate-50 py-1.5 pl-7 pr-2 text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <svg class="size-3.5 text-slate-400 absolute left-2 top-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                </div>

                <!-- Groups List -->
                <div class="mt-1 space-y-0.5 text-xs">
                    @foreach($groups as $grp)
                    <a href="{{ route('whatsapp.index', ['group' => $grp['name']]) }}" class="flex items-center justify-between px-3 py-1.5 rounded-md transition {{ $groupFilter === $grp['name'] ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="truncate pr-1">
                            <div class="truncate text-xs font-semibold">{{ $grp['name'] }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">WhatsApp Official</div>
                        </div>
                        <span class="rounded-full bg-blue-600 text-white px-1.5 py-0.2 text-[10px] font-bold shrink-0">{{ $grp['badge'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Custom Views Section -->
            <div class="mt-auto border-t border-slate-100 p-3">
                <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase">
                    <span>CUSTOM VIEWS</span>
                    <button type="button" class="text-slate-400 hover:text-slate-600">+</button>
                </div>
                <div class="mt-2 text-[11px] text-slate-400 italic">
                    Belum ada Custom View
                </div>
            </div>
        </nav>

        <!-- ======================================================== -->
        <!-- COLUMN 3: Chat Conversation List (280px - 320px)         -->
        <!-- ======================================================== -->
        <section class="w-72 lg:w-80 bg-white border-r border-slate-200 flex flex-col shrink-0 overflow-hidden">
            <!-- Header Filter Bar -->
            <div class="p-3 border-b border-slate-100 space-y-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-900 capitalize">{{ $groupFilter ?: ($folder === 'all' ? 'All chats' : str_replace('_', ' ', $folder)) }}</h2>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="hover:text-slate-700 cursor-pointer">WhatsApp ▾</span>
                        <span class="hover:text-slate-700 cursor-pointer">Newest ▾</span>
                    </div>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('whatsapp.index') }}">
                    @if($folder) <input type="hidden" name="folder" value="{{ $folder }}"> @endif
                    @if($groupFilter) <input type="hidden" name="group" value="{{ $groupFilter }}"> @endif
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, phone, message..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-3 text-xs text-slate-700 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <svg class="size-3.5 text-slate-400 absolute left-2.5 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                </form>

                <!-- Filter Row -->
                <div class="flex items-center gap-2 text-[11px]">
                    <select onchange="window.location.href='{{ route('whatsapp.index', ['folder' => $folder]) }}&tag=' + this.value" class="rounded-md border border-slate-200 bg-white py-1 px-2 text-slate-600 focus:outline-none">
                        <option value="">Semua Tag</option>
                        <option value="potensi" {{ $tagFilter === 'potensi' ? 'selected' : '' }}>Potensi</option>
                        <option value="keep" {{ $tagFilter === 'keep' ? 'selected' : '' }}>Keep</option>
                        <option value="cicilan" {{ $tagFilter === 'cicilan' ? 'selected' : '' }}>Cicilan</option>
                        <option value="infinix" {{ $tagFilter === 'infinix' ? 'selected' : '' }}>Infinix</option>
                        <option value="samsung" {{ $tagFilter === 'samsung' ? 'selected' : '' }}>Samsung</option>
                        <option value="iphone" {{ $tagFilter === 'iphone' ? 'selected' : '' }}>iPhone</option>
                    </select>

                    <select class="rounded-md border border-slate-200 bg-white py-1 px-2 text-slate-600 focus:outline-none">
                        <option value="">Filter by Agent</option>
                        <option value="zofia">zofia</option>
                        <option value="{{ $currentUser->name }}">{{ $currentUser->name }}</option>
                    </select>
                </div>
            </div>

            <!-- Scrollable Chats List -->
            <div class="flex-1 custom-scroll overflow-y-auto divide-y divide-slate-100">
                @forelse($chats as $c)
                @php
                    $isActive = $activeChat && $activeChat->id === $c->id;
                    $initial = strtoupper(substr($c->customer_name, 0, 1));
                    $colors = ['bg-rose-100 text-rose-700', 'bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-purple-100 text-purple-700', 'bg-amber-100 text-amber-700'];
                    $color = $colors[crc32($c->customer_name) % count($colors)];
                @endphp
                <a href="{{ route('whatsapp.index', ['chat_id' => $c->id, 'folder' => $folder, 'group' => $groupFilter]) }}" class="block px-3 py-3 transition hover:bg-slate-50 {{ $isActive ? 'bg-blue-50/70 border-l-4 border-blue-600' : '' }}">
                    <div class="flex items-start gap-2.5">
                        <input type="checkbox" onclick="event.stopPropagation()" class="mt-1 size-3.5 rounded border-slate-300 text-blue-600 focus:ring-0">
                        
                        <!-- Avatar -->
                        <div class="size-9 rounded-full {{ $color }} font-black text-xs flex items-center justify-center shrink-0">
                            {{ $initial }}
                        </div>

                        <!-- Chat Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-slate-900 truncate">{{ $c->customer_name }}</h3>
                                <span class="text-[10px] text-slate-400 shrink-0">
                                    {{ $c->last_message_at ? $c->last_message_at->format('H:i') : '' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 mt-0.5">
                                <span class="truncate">WhatsApp Official</span>
                                <span class="text-[10px] font-semibold text-blue-600 shrink-0">Assigned</span>
                            </div>

                            <div class="mt-1 flex items-center justify-between">
                                <p class="text-xs text-slate-600 truncate pr-2">
                                    {{ $c->last_message }}
                                </p>
                                @if($c->unread_count > 0)
                                <span class="size-4 rounded-full bg-rose-500 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                                    {{ $c->unread_count }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-xs text-slate-400">
                    Tidak ada obrolan di folder ini.
                </div>
                @endforelse
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- COLUMN 4: Active Chat Conversation Window (Flex-1)       -->
        <!-- ======================================================== -->
        <main class="flex-1 flex flex-col bg-[#eef1f5] overflow-hidden min-w-[360px]">
            @if($activeChat)
            <!-- Active Chat Header -->
            <div class="h-14 bg-white border-b border-slate-200 px-4 flex items-center justify-between shrink-0 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="size-9 rounded-full bg-blue-100 text-blue-700 font-black text-xs flex items-center justify-center ring-1 ring-blue-300">
                        {{ strtoupper(substr($activeChat->customer_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xs font-black text-slate-900">{{ $activeChat->customer_name }}</h2>
                            <span class="rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.2 text-[10px] font-bold">WhatsApp Official</span>
                        </div>
                        <div class="text-[10px] text-slate-400 font-medium">
                            +{{ $activeChat->phone_number }} &bull; {{ $activeChat->cabang }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Tambah Agent Button -->
                    <button type="button" onclick="alert('Chat ini telah ditugaskan ke Staff Zofia & Anda.')" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Tambah Agent
                    </button>

                    <!-- Resolve Button -->
                    <form method="POST" action="{{ route('whatsapp.resolve', $activeChat->id) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-700 transition shadow-xs">
                            Resolve
                        </button>
                    </form>
                </div>
            </div>

            <!-- Messages Stream Area -->
            <div id="messages-container" class="flex-1 custom-scroll overflow-y-auto p-4 space-y-3">
                @foreach($activeChat->messages as $msg)
                    @if($msg->sender_type === 'customer')
                    <!-- Customer Message Bubble (Left) -->
                    <div class="flex items-start gap-2.5 max-w-[80%]">
                        <div class="size-7 rounded-full bg-slate-300 text-slate-700 text-xs font-bold flex items-center justify-center shrink-0 mt-1">
                            {{ strtoupper(substr($activeChat->customer_name, 0, 1)) }}
                        </div>
                        <div class="bg-white rounded-2xl rounded-tl-xs border border-slate-200 p-3 shadow-xs">
                            @if($msg->media_url)
                            <div class="mb-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 max-w-sm">
                                <img src="{{ $msg->media_url }}" alt="Attachment" class="w-full max-h-60 object-contain cursor-pointer hover:opacity-95" onclick="window.open(this.src, '_blank')">
                            </div>
                            @endif
                            <p class="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $msg->message }}</p>
                            <div class="text-[10px] text-slate-400 text-right mt-1">
                                {{ $msg->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Agent Message Bubble (Right) -->
                    <div class="flex flex-col items-end max-w-[80%] ml-auto">
                        <div class="bg-[#e8f1fd] border border-blue-200/60 rounded-2xl rounded-tr-xs p-3 shadow-xs text-slate-800">
                            <div class="text-[11px] font-bold text-blue-700 mb-1">
                                {{ $msg->agent_name ?: 'Staff' }}
                            </div>
                            <p class="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $msg->message }}</p>
                            <div class="flex items-center justify-end gap-1 text-[10px] text-blue-500/80 mt-1">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                <svg class="size-3 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- Quick Action Buttons Bar (Share Promo, Cek Katalog, etc.) -->
            <div class="bg-white/80 border-t border-slate-200/80 px-4 py-2 flex items-center gap-2 overflow-x-auto custom-scroll shrink-0">
                <button type="button" onclick="insertQuickReply('share_promo')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-blue-400 transition shrink-0 shadow-2xs">
                    <span>📎</span> Share Promo
                </button>
                <button type="button" onclick="insertQuickReply('katalog')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-blue-400 transition shrink-0 shadow-2xs">
                    <span>📋</span> Cek Katalog
                </button>
                <button type="button" onclick="insertQuickReply('simulasi_cicilan')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-blue-400 transition shrink-0 shadow-2xs">
                    <span>🧮</span> Simulasi Cicilan
                </button>
                <button type="button" onclick="insertQuickReply('shopee')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-blue-400 transition shrink-0 shadow-2xs">
                    <span>🛍️</span> Shopee
                </button>
            </div>

            <!-- Chat Input Area -->
            <div class="bg-white border-t border-slate-200 p-3 shrink-0">
                <form id="chat-form" method="POST" action="{{ route('whatsapp.send', $activeChat->id) }}" onsubmit="submitChatMessage(event)">
                    @csrf
                    <div class="relative rounded-xl border border-slate-200 bg-slate-50 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition">
                        <textarea id="chat-input" name="message" rows="2" placeholder='Type "shift + enter" to add a new line. Type "/" to use quick reply' class="w-full bg-transparent px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none resize-none" onkeydown="handleTextareaKeydown(event)"></textarea>

                        <div class="flex items-center justify-between px-3 py-2 border-t border-slate-100 bg-white/50 rounded-b-xl">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="alert('Fitur lampirkan file & gambar aktif')" title="Lampirkan File / Gambar" class="text-slate-400 hover:text-slate-600 transition">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                </button>
                            </div>

                            <button type="submit" id="send-btn" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-blue-700 transition shadow-xs">
                                <span>Send</span>
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <!-- No Active Chat Selected -->
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                <svg class="size-16 text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <h3 class="text-sm font-bold text-slate-700">Pilih Obrolan</h3>
                <p class="text-xs text-slate-400 mt-1">Pilih salah satu customer dari daftar di sebelah kiri untuk mulai melayani chat.</p>
            </div>
            @endif
        </main>

        <!-- ======================================================== -->
        <!-- COLUMN 5: Right CRM Panel (320px - 380px)                -->
        <!-- ======================================================== -->
        @if($activeChat)
        <aside class="w-80 lg:w-96 bg-white border-l border-slate-200 flex flex-col shrink-0 custom-scroll overflow-y-auto p-4 space-y-4">
            <!-- Block Customer Button -->
            <form method="POST" action="{{ route('whatsapp.block', $activeChat->id) }}">
                @csrf
                <button type="button" onclick="confirmForm(this, { title: 'Blokir Customer Ini?', message: 'Customer ini akan diblokir dari antrean interaksi percakapan.', confirmText: 'Ya, Blokir Customer', type: 'warning' })" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50/60 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">
                    <svg class="size-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                    Block Customer
                </button>
            </form>

            <!-- Tags Manager -->
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-700">Tags</label>
                <div class="relative">
                    <input type="text" id="tag-input" placeholder="+ Cari atau ketik tag baru..." onkeydown="handleTagInput(event)" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-3 text-xs text-slate-700 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Suggestions / SARAN TAG -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SARAN TAG</span>
                    <button type="button" onclick="addTag('no user')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-100">+ no user</button>
                    <button type="button" onclick="addTag('potensi')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-100">+ potensi</button>
                    <button type="button" onclick="addTag('ho')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-100">+ ho</button>
                </div>

                <!-- Active Tags -->
                <div id="active-tags-container" class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($activeChat->tags as $t)
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[11px] font-bold text-blue-700">
                        <span>#{{ $t }}</span>
                        <button type="button" onclick="removeTag('{{ $t }}')" class="hover:text-rose-600">&times;</button>
                    </span>
                    @endforeach
                </div>
            </div>

            <!-- CRM Data Form -->
            <form id="crm-form" method="POST" action="{{ route('whatsapp.crm', $activeChat->id) }}" class="space-y-3 pt-2 border-t border-slate-100">
                @csrf
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-900">CRM Data</span>
                    <span class="rounded bg-blue-50 text-blue-600 border border-blue-200 px-2 py-0.2 text-[10px] font-bold">sosmed-ho</span>
                </div>

                <!-- Hidden inputs for tags array -->
                <div id="hidden-tags-container">
                    @foreach($activeChat->tags as $t)
                    <input type="hidden" name="tags[]" value="{{ $t }}">
                    @endforeach
                </div>

                <!-- Jenis Format -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Format <span class="text-rose-500">*</span></label>
                    <select name="format_type" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="Keep" {{ $activeChat->format_type === 'Keep' ? 'selected' : '' }}>Keep</option>
                        <option value="Follow Up" {{ $activeChat->format_type === 'Follow Up' ? 'selected' : '' }}>Follow Up</option>
                        <option value="Booking" {{ $activeChat->format_type === 'Booking' ? 'selected' : '' }}>Booking</option>
                        <option value="Tanya Promo" {{ $activeChat->format_type === 'Tanya Promo' ? 'selected' : '' }}>Tanya Promo</option>
                        <option value="Komplain" {{ $activeChat->format_type === 'Komplain' ? 'selected' : '' }}>Komplain</option>
                    </select>
                </div>

                <!-- Cabang -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Cabang <span class="text-rose-500">*</span></label>
                    <select name="cabang" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="TANPA CABANG" {{ $activeChat->cabang === 'TANPA CABANG' ? 'selected' : '' }}>TANPA CABANG</option>
                        <option value="GM AMT" {{ $activeChat->cabang === 'GM AMT' ? 'selected' : '' }}>GM AMT</option>
                        <option value="GM TJG" {{ $activeChat->cabang === 'GM TJG' ? 'selected' : '' }}>GM TJG</option>
                        <option value="GM KPS" {{ $activeChat->cabang === 'GM KPS' ? 'selected' : '' }}>GM KPS</option>
                        <option value="GM SDN" {{ $activeChat->cabang === 'GM SDN' ? 'selected' : '' }}>GM SDN (SUNGAI DANAU)</option>
                        <option value="GM BTC" {{ $activeChat->cabang === 'GM BTC' ? 'selected' : '' }}>GM BTC</option>
                        <option value="GM BJB" {{ $activeChat->cabang === 'GM BJB' ? 'selected' : '' }}>GM BJB</option>
                    </select>
                </div>

                <!-- Nama Customer -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Customer <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ $activeChat->customer_name }}" required class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Nomor Whatsapp -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Nomor Whatsapp <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone_number" value="{{ $activeChat->phone_number }}" required class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Barang Keep -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Barang Keep <span class="text-rose-500">*</span></label>
                    <input type="text" name="barang_keep" value="{{ $activeChat->barang_keep }}" placeholder="e.g. Infinix XPAD 20 PRO 8/256GB" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Tanggal Pengambilan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Pengambilan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pengambilan" value="{{ $activeChat->tanggal_pengambilan ? $activeChat->tanggal_pengambilan->format('Y-m-d') : date('Y-m-d') }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none">{{ $activeChat->notes }}</textarea>
                </div>

                <!-- Kode Agent -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Kode Agent <span class="text-rose-500">*</span></label>
                    <select name="agent_code" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="zofia">zofia</option>
                        <option value="{{ $currentUser->name }}" selected>{{ $currentUser->name }}</option>
                    </select>
                </div>

                <!-- Simpan dan Kirim Button -->
                <button type="submit" name="action" value="save_and_send" class="w-full rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-600/30 hover:bg-blue-700 transition">
                    Simpan dan Kirim
                </button>
            </form>

            <!-- Conversation History Accordion -->
            <div class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs font-bold text-slate-800 mb-2">
                    <span>Conversation History</span>
                    <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[10px] text-slate-500 font-semibold">{{ $activeChat->messages->count() }} Sesi</span>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 text-[11px] text-slate-600 space-y-1 max-h-36 custom-scroll overflow-y-auto">
                    @foreach($activeChat->messages->take(-3) as $h)
                    <div class="truncate">
                        <strong class="{{ $h->sender_type === 'agent' ? 'text-blue-600' : 'text-slate-700' }}">{{ $h->sender_type === 'agent' ? ($h->agent_name ?: 'Staff') : 'Customer' }}:</strong>
                        <span>{{ $h->message }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </aside>
        @endif

    </div>

    <!-- Modal Mulai Chat Baru -->
    <div id="new-chat-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-black text-slate-900">Mulai Chat Baru</h3>
                <button type="button" onclick="closeNewChatModal()" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('whatsapp.start') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Customer</label>
                    <input type="text" name="customer_name" required placeholder="e.g. Kaka Gadget" class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp (diawali 08/628)</label>
                    <input type="text" name="phone_number" required placeholder="081234567890" class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Produk / Barang yang Ditanyakan (Opsional)</label>
                    <input type="text" name="item_code" placeholder="e.g. Redmi Note 13 8/256GB" class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeNewChatModal()" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Mulai Chat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Actions & Templates -->
    <script>
        // Scroll messages to bottom on load
        window.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Online / Offline Toggle
        function toggleStatus() {
            const label = document.getElementById('agent-status-label');
            const dot = document.getElementById('toggle-status-dot');
            const btn = document.getElementById('toggle-status-btn');

            if (label.innerText === 'ONLINE') {
                label.innerText = 'OFFLINE';
                btn.classList.remove('bg-emerald-500');
                btn.classList.add('bg-slate-300');
                dot.classList.remove('translate-x-4');
                dot.classList.add('translate-x-0');
            } else {
                label.innerText = 'ONLINE';
                btn.classList.remove('bg-slate-300');
                btn.classList.add('bg-emerald-500');
                dot.classList.remove('translate-x-0');
                dot.classList.add('translate-x-4');
            }
        }

        // Quick Reply Templates
        const templates = {
            share_promo: "Halo Kak! ✨ Berikut promo spesial Gadgetmart hari ini: Dapatkan cashback hingga Rp 500.000 untuk pembelian smartphone pilihan & cicilan bunga 0%! Promo berlaku terbatas ya Kak. Mau Mimin bantu amankan promonya sekarang? 🎁",
            katalog: "Halo Kak! 📋 Silakan cek katalog lengkap produk dan harga terupdate Gadgetmart melalui link resmi kami di https://gadgetmart.id/katalog atau beri tahu Mimin produk yang sedang Kakak cari ya!",
            simulasi_cicilan: "Halo Kak! 🧮 Simulasi cicilan di Gadgetmart sangat fleksibel:\n- Tenor 3 Bulan: DP 0%\n- Tenor 6 Bulan: Angsuran ringan\n- Tenor 12 Bulan: Bunga kompetitif\nBisa lewat Kredivo, Akulaku, Home Credit, atau Kartu Kredit. Mau coba hitungkan untuk produk yang mana Kak?",
            shopee: "Halo Kak! 🛍️ Untuk transaksi melalui Shopee Official Store Gadgetmart, Kakak bisa langsung kunjungi toko kami dengan jaminan 100% Original, Garansi Resmi, dan Gratis Ongkir!"
        };

        function insertQuickReply(type) {
            const input = document.getElementById('chat-input');
            if (!input) return;
            const text = templates[type] || '';
            input.value = text;
            input.focus();
        }

        function showQuickRepliesPalette() {
            insertQuickReply('share_promo');
        }

        // Keydown handler in textarea: Enter sends, Shift+Enter newlines
        function handleTextareaKeydown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                document.getElementById('chat-form').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        }

        // Submit Chat Message
        function submitChatMessage(event) {
            const input = document.getElementById('chat-input');
            if (!input || !input.value.trim()) {
                event.preventDefault();
                return;
            }
            // Form posts naturally to route('whatsapp.send')
        }

        // Tags Management
        function addTag(tagName) {
            const container = document.getElementById('active-tags-container');
            const hiddenContainer = document.getElementById('hidden-tags-container');
            if (!container || !hiddenContainer) return;

            // Check duplicate
            const existing = Array.from(hiddenContainer.querySelectorAll('input')).map(i => i.value.toLowerCase());
            if (existing.includes(tagName.toLowerCase())) return;

            // Add hidden input
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'tags[]';
            input.value = tagName;
            input.id = 'tag-hidden-' + tagName.replace(/\s+/g, '-');
            hiddenContainer.appendChild(input);

            // Add visual pill
            const pill = document.createElement('span');
            pill.id = 'tag-pill-' + tagName.replace(/\s+/g, '-');
            pill.className = 'inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[11px] font-bold text-blue-700';
            pill.innerHTML = `<span>#${tagName}</span><button type="button" onclick="removeTag('${tagName}')" class="hover:text-rose-600">&times;</button>`;
            container.appendChild(pill);
        }

        function removeTag(tagName) {
            const safeId = tagName.replace(/\s+/g, '-');
            const hidden = document.getElementById('tag-hidden-' + safeId);
            const pill = document.getElementById('tag-pill-' + safeId);
            if (hidden) hidden.remove();
            if (pill) pill.remove();
        }

        function handleTagInput(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const val = event.target.value.trim();
                if (val) {
                    addTag(val);
                    event.target.value = '';
                }
            }
        }

        // Modal Mulai Chat Baru
        function openNewChatModal() {
            document.getElementById('new-chat-modal').classList.remove('hidden');
        }
        function closeNewChatModal() {
            document.getElementById('new-chat-modal').classList.add('hidden');
        }
    </script>
</body>
</html>

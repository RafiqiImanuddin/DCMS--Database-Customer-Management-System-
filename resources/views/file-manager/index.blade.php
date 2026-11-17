@extends('layouts.app')

@section('title', 'File Manager Klaim AR - Dashboard Manager')

@section('content')
<div class="space-y-6">
    <!-- 1. Header & Action Bar -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span>Dashboard Manager</span>
                <span>/</span>
                <span class="text-brand-600 font-bold">File Manager</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl flex items-center gap-2.5">
                <span>File Manager Klaim AR</span>
                <span class="rounded-full bg-amber-100 border border-amber-200/80 px-3 py-0.5 text-xs font-bold text-amber-800">
                    {{ count($folders) }} Folder Cabang
                </span>
            </h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Pusat berkas klaim faktur AR terorganisir per cabang toko, checklist berkas terpadu, dan distribusi berkas ke PIC WhatsApp.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ \App\Support\RouteCipher::url('workspace-sosmed.index', ['tab' => 6]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                <span>Buka Klaim AR</span>
            </a>
            <button type="button" onclick="window.location.reload()" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                <span>Refresh Berkas</span>
            </button>
        </div>
    </div>

    <!-- 2. Metric Cards: Ringkasan File Manager -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <!-- Total Folder Cabang -->
        <div class="p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Folder Cabang</span>
                <div class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-800 tracking-tight">{{ number_format($stats['total_folders']) }}</div>
            <div class="mt-0.5 text-[11px] text-slate-500 font-medium">Toko Aktif Terdata</div>
        </div>

        <!-- Total Berkas / Faktur Klaim -->
        <div class="p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider text-teal-600">Total Berkas AR</span>
                <div class="grid size-7 place-items-center rounded-lg bg-teal-50 text-teal-600">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-teal-700 tracking-tight">{{ number_format($stats['total_items']) }}</div>
            <div class="mt-0.5 text-[11px] text-teal-600 font-semibold">Transaksi Klaim AR</div>
        </div>

        <!-- Total Potensi Denda -->
        <div class="p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Potensi Denda</span>
                <div class="grid size-7 place-items-center rounded-lg bg-rose-50 text-rose-600">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-700 tracking-tight">Rp {{ number_format($stats['total_penalty'], 0, ',', '.') }}</div>
            <div class="mt-0.5 text-[11px] text-rose-600 font-semibold">Unit HP Belum Sosmed</div>
        </div>

        <!-- Total Omzet Belum Sosmed -->
        <div class="p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Nilai Transaksi</span>
                <div class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-indigo-700 tracking-tight">Rp {{ number_format($stats['total_omzet'], 0, ',', '.') }}</div>
            <div class="mt-0.5 text-[11px] text-indigo-600 font-semibold">Omzet Faktur Terdata</div>
        </div>
    </div>

    <!-- 3. Folder Cabang Visual Strip (Quick Filter Folders) -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <svg class="size-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                <span>Folder Berkas Cabang (Klik untuk Filter)</span>
            </h2>
            @if($selectedCabang !== 'ALL')
                <a href="{{ route('file-manager.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-800 transition">
                    &larr; Tampilkan Semua Cabang
                </a>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
            @forelse($folders as $cab => $info)
                <a href="{{ route('file-manager.index', ['cabang' => $cab]) }}"
                    class="group p-3 rounded-xl border transition-all duration-150 {{ $selectedCabang === $cab ? 'border-amber-400 bg-amber-50/50 ring-2 ring-amber-400 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs' }}">
                    <div class="flex items-start justify-between">
                        <div class="grid size-8 place-items-center rounded-lg {{ $selectedCabang === $cab ? 'bg-amber-100 text-amber-700' : 'bg-amber-50 text-amber-600 group-hover:scale-105' }} transition">
                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-1.5V9a3 3 0 0 0-3-3h-3.379a1.5 1.5 0 0 1-1.06-.44L8.439 3.44A3 3 0 0 0 6.318 2.5H4.5A3 3 0 0 0 1.5 5.5v12.5A3 3 0 0 0 4.5 21h15Z"/></svg>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-extrabold text-slate-700">
                            {{ $info['count'] }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs font-black text-slate-900 group-hover:text-amber-800 transition truncate">{{ $cab }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold truncate">
                            {{ count($info['pics']) > 0 ? implode(', ', array_slice(array_keys($info['pics']), 0, 2)) : 'Semua PIC' }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-400 font-medium">
                    Belum ada folder cabang yang terbentuk dari data klaim AR.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 4. Floating / Sticky Batch Checklist Action Bar (Muncul Otomatis saat Checkbox Dicentang) -->
    <div id="batchActionBar" class="hidden items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-900 text-white shadow-xl transition-all duration-200">
        <div class="flex items-center gap-3">
            <span class="grid size-7 place-items-center rounded-lg bg-white/20 text-xs font-black" id="selectedCountBadge">0</span>
            <div>
                <div class="text-xs font-bold" id="selectedCountText">0 Berkas Cabang Terpilih</div>
                <div class="text-[11px] text-slate-300 font-medium" id="selectedTotalItemsText">0 Transaksi Faktur AR</div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openBatchWhatsAppModal()" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Kirim ke PIC (WhatsApp)</span>
            </button>
            <button type="button" onclick="downloadSelectedBatchZip()" class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Unduh Paket Terpilih (.ZIP)</span>
            </button>
            <button type="button" onclick="clearAllCheckboxes()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition" title="Batalkan Pilihan">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </div>

    <!-- 5. Search & Filter Bar -->
    <div class="p-4 rounded-2xl border border-slate-200/80 bg-white shadow-2xs space-y-3">
        <form method="GET" action="{{ route('file-manager.index') }}" class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <input type="text" name="q" value="{{ $searchQuery }}" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly')" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pl-10 pr-4 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand-500 focus:outline-none transition shadow-2xs"
                    placeholder="Cari nama berkas Excel, cabang toko, atau nama PIC...">
            </div>

            <div class="flex items-center gap-2">
                <select name="cabang" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-brand-500 focus:outline-none transition">
                    <option value="ALL" {{ $selectedCabang === 'ALL' ? 'selected' : '' }}>Semua Cabang Toko</option>
                    @foreach($folders as $cab => $inf)
                        <option value="{{ $cab }}" {{ $selectedCabang === $cab ? 'selected' : '' }}>{{ $cab }} ({{ $inf['count'] }} AR)</option>
                    @endforeach
                </select>

                @if($searchQuery || $selectedCabang !== 'ALL')
                    <a href="{{ route('file-manager.index') }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition" title="Reset filter">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 6. Daftar Berkas Tabel File Manager (Checklist Interface) -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600">
                        <th class="py-3 px-4 w-10 text-center">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllFiles(this.checked)" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                        </th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px]">NAMA BERKAS / FILE</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px] text-center">CABANG</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px] text-center">JUMLAH FAKTUR</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px] text-right">TOTAL OMZET</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px] text-center">STATUS DENDA</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px]">TARGET PIC SOSMED</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px] text-center">STATUS KIRIM</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-[11px] text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($files as $f)
                        <tr class="hover:bg-slate-50/80 transition-colors file-row" id="row-{{ $f['cabang'] }}">
                            <td class="py-3.5 px-4 text-center">
                                <input type="checkbox"
                                    class="file-checkbox size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                    value="{{ $f['cabang'] }}"
                                    data-cabang="{{ $f['cabang'] }}"
                                    data-filename="{{ $f['name'] }}"
                                    data-items="{{ $f['items_count'] }}"
                                    data-penalty="{{ $f['total_penalty'] }}"
                                    data-pic="{{ $f['primary_pic'] }}"
                                    data-phone="{{ $f['pic_phone'] }}"
                                    onchange="handleFileCheckboxChange()">
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate flex items-center gap-2">
                                            <span>{{ $f['name'] }}</span>
                                            <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[9px] font-extrabold uppercase text-slate-600">.XLSX</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            Diperbarui: {{ date('d M Y') }} &bull; Paket Rekap Excel Cabang
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="rounded-lg bg-amber-50 border border-amber-200/80 px-2.5 py-1 text-xs font-black text-amber-800">
                                    {{ $f['cabang'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800 tabular-nums">
                                {{ number_format($f['items_count']) }} Transaksi
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-800 tabular-nums">
                                Rp {{ number_format($f['total_omzet'], 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($f['total_penalty'] > 0)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-700">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        Rp {{ number_format($f['total_penalty'], 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                        <span class="size-1.5 rounded-full bg-slate-300"></span>
                                        Rp 0 (Pass)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>{{ $f['primary_pic'] }}</span>
                                    @if(count($f['pics']) > 1)
                                        <span class="rounded-full bg-slate-100 px-1.5 py-0.2 text-[9px] font-bold text-slate-600" title="{{ implode(', ', $f['pics']) }}">+{{ count($f['pics']) - 1 }}</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 font-mono">
                                    {{ $f['pic_phone'] ? '+'.$f['pic_phone'] : 'Nomor belum terisi' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($f['status'] === 'Sudah Di Share')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[11px] font-bold text-blue-700">
                                        <span class="size-1.5 rounded-full bg-blue-500"></span>
                                        Sudah Di Share
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        Siap Dikirim
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('file-manager.download', ['cabang' => $f['cabang']]) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs" title="Unduh Berkas Excel Cabang Ini">
                                        <svg class="size-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <span>Unduh</span>
                                    </a>
                                    <button type="button" onclick="openSingleWhatsAppModal('{{ $f['cabang'] }}', '{{ $f['primary_pic'] }}', '{{ $f['pic_phone'] }}')" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-2.5 py-1 text-xs font-bold text-white transition shadow-2xs cursor-pointer" title="Kirim Berkas ke PIC via WhatsApp">
                                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <span>Kirim WA</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs font-medium">
                                Tidak ada berkas klaim AR yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between p-4 border-t border-slate-100 bg-slate-50/60 text-xs font-semibold text-slate-500">
            <div>Menampilkan {{ count($files) }} dari {{ count($folders) }} Folder Cabang</div>
            <div>Format Otomatis: Microsoft Excel (.xlsx)</div>
        </div>
    </div>
</div>

<!-- Modal Dialog: Kirim Berkas Klaim AR ke WhatsApp PIC -->
<div id="whatsappDistributionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display:none;" onclick="if(event.target===this) closeWhatsAppModal()">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white shadow-2xl transition-all overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-emerald-50/60">
            <div class="flex items-center gap-2.5">
                <div class="grid size-9 place-items-center rounded-xl bg-emerald-600 text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Kirim Berkas Klaim AR ke PIC WhatsApp</h3>
                    <p class="text-[11px] font-medium text-slate-500" id="waModalSubtitle">Distribusi berkas terformat otomatis</p>
                </div>
            </div>
            <button type="button" class="rounded-xl p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer" onclick="closeWhatsAppModal()">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <!-- Pilihan PIC & No WhatsApp -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="waInputPicName">Nama PIC</label>
                    <input type="text" id="waInputPicName" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:border-brand-500 focus:outline-none transition" placeholder="Contoh: QONA / RYAN">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1" for="waInputPhone">Nomor WhatsApp Tujuan</label>
                    <input type="text" id="waInputPhone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:border-brand-500 focus:outline-none transition" placeholder="Contoh: 6281234567890">
                </div>
            </div>

            <!-- Pilih dari data Karyawan Cepat -->
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pilih Cepat dari Kontak Karyawan:</label>
                <select id="waEmployeeSelect" onchange="applyEmployeeToModal(this.value)" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-1.5 text-xs font-medium text-slate-700 focus:bg-white focus:outline-none transition">
                    <option value="">-- Pilih Kontak Karyawan / Admin --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->phone }}" data-name="{{ $emp->name }}">{{ $emp->name }} ({{ $emp->role }} - {{ $emp->cabang ?? 'Pusat' }}) - {{ $emp->phone }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Preview Pesan WhatsApp -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Teks Notifikasi WhatsApp</label>
                <textarea id="waPreviewText" rows="7" class="w-full rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-xs font-mono text-slate-800 focus:bg-white focus:border-brand-500 focus:outline-none transition leading-relaxed"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3.5 bg-slate-50/50">
            <button type="button" onclick="copyWhatsAppMessage()" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition cursor-pointer">
                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span id="btnCopyWaText">Salin Teks</span>
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeWhatsAppModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="button" onclick="launchWhatsAppDirect()" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-2xs transition cursor-pointer">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Buka WhatsApp &amp; Kirim</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSelectedCabang = [];

function toggleSelectAllFiles(isChecked) {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = isChecked;
    });
    handleFileCheckboxChange();
}

function handleFileCheckboxChange() {
    const checkboxes = document.querySelectorAll('.file-checkbox:checked');
    currentSelectedCabang = Array.from(checkboxes).map(cb => cb.value);

    const bar = document.getElementById('batchActionBar');
    const badge = document.getElementById('selectedCountBadge');
    const text = document.getElementById('selectedCountText');
    const itemsText = document.getElementById('selectedTotalItemsText');
    const selectAll = document.getElementById('selectAllCheckbox');

    let totalItems = 0;
    checkboxes.forEach(cb => {
        totalItems += parseInt(cb.dataset.items || '0', 10);
    });

    if (currentSelectedCabang.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        badge.innerText = currentSelectedCabang.length;
        text.innerText = `${currentSelectedCabang.length} Berkas Cabang Terpilih`;
        itemsText.innerText = `Total ${totalItems.toLocaleString('id-ID')} Transaksi Faktur AR`;
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
        if (selectAll) selectAll.checked = false;
    }
}

function clearAllCheckboxes() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) selectAll.checked = false;
    handleFileCheckboxChange();
}

function downloadSelectedBatchZip() {
    if (currentSelectedCabang.length === 0) {
        alert("Pilih setidaknya satu berkas cabang untuk diunduh.");
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('file-manager.download_batch') }}";

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = "{{ csrf_token() }}";
    form.appendChild(csrfInput);

    currentSelectedCabang.forEach(cab => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cabang_list[]';
        input.value = cab;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function openSingleWhatsAppModal(cabang, picName, phone) {
    currentSelectedCabang = [cabang];
    fetchWhatsAppPreview(picName, phone);
}

function openBatchWhatsAppModal() {
    if (currentSelectedCabang.length === 0) {
        alert("Pilih setidaknya satu cabang.");
        return;
    }
    fetchWhatsAppPreview('', '');
}

function fetchWhatsAppPreview(picName, phone) {
    fetch("{{ route('file-manager.send_pic') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({
            cabang_list: currentSelectedCabang,
            pic_name: picName,
            phone: phone
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || "Gagal memproses notifikasi.");
            return;
        }

        document.getElementById('waInputPicName').value = data.pic_name || '';
        document.getElementById('waInputPhone').value = data.phone || '';
        document.getElementById('waPreviewText').value = data.message_preview || '';
        document.getElementById('waModalSubtitle').innerText = `${currentSelectedCabang.length} Cabang Terpilih (${data.total_items} Faktur AR)`;

        const modal = document.getElementById('whatsappDistributionModal');
        if (modal) modal.style.display = 'flex';
    })
    .catch(err => {
        console.error(err);
        alert("Terjadi kesalahan jaringan.");
    });
}

function closeWhatsAppModal() {
    const modal = document.getElementById('whatsappDistributionModal');
    if (modal) modal.style.display = 'none';
}

function applyEmployeeToModal(phone) {
    const select = document.getElementById('waEmployeeSelect');
    const selectedOpt = select.options[select.selectedIndex];
    if (!phone) return;

    const picName = selectedOpt.dataset.name || '';
    document.getElementById('waInputPicName').value = picName;
    document.getElementById('waInputPhone').value = phone;

    // Refresh pesan preview dengan nama PIC baru
    fetchWhatsAppPreview(picName, phone);
}

function copyWhatsAppMessage() {
    const text = document.getElementById('waPreviewText').value;
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('btnCopyWaText');
        if (btn) {
            btn.innerText = 'Tersalin!';
            setTimeout(() => { btn.innerText = 'Salin Teks'; }, 2000);
        }
    });
}

function launchWhatsAppDirect() {
    const phone = document.getElementById('waInputPhone').value.replace(/\D/g, '');
    const text = document.getElementById('waPreviewText').value;
    const encoded = encodeURIComponent(text);

    let url = '';
    if (phone) {
        let cleanPhone = phone;
        if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.slice(1);
        url = `https://wa.me/${cleanPhone}?text=${encoded}`;
    } else {
        url = `https://api.whatsapp.com/send?text=${encoded}`;
    }

    window.open(url, '_blank');
}
</script>
@endsection

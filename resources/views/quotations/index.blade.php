@extends('layouts.app')

@section('title', 'Surat Penawaran Harga - Dashboard Manager')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-700">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                    Dashboard Manager
                </span>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-500">Surat Penawaran</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Surat Penawaran Harga</h1>
            <p class="text-xs text-slate-500 sm:text-sm">Buat, cetak, dan kelola dokumen penawaran harga resmi Gadget Mart dengan format standar perusahaan.</p>
        </div>

        <div class="flex items-center gap-2">
            @if(!auth()->check() || !auth()->user()->isGuest())
            <a href="{{ route('quotations.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Buat Surat Penawaran</span>
            </a>
            @else
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-900">
                🔒 Mode Tamu: Read-Only
            </span>
            @endif
        </div>
    </div>

    <!-- Alert Success -->
    @if (session('success'))
    <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Surat Dibuat</span>
                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($stats['total_count']) }}</div>
            <p class="mt-1 text-xs text-slate-500">Arsip surat penawaran harga tersimpan</p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Bulan Ini</span>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($stats['this_month_count']) }} <span class="text-xs font-bold text-slate-400">Dokumen</span></div>
            <p class="mt-1 text-xs text-slate-500">Dibuat pada periode {{ date('F Y') }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Akumulasi Nilai</span>
                <span class="rounded-lg bg-purple-50 p-2 text-purple-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-purple-600">{{ \App\Support\DataMasker::maskCurrency($stats['total_amount']) }}</div>
            <p class="mt-1 text-xs text-slate-500">Estimasi nilai penawaran barang diajukan</p>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="border-b border-slate-200/80 bg-slate-50/50 p-4">
            <form method="GET" action="{{ route('quotations.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari nomor surat, pelanggan, penandatangan, atau nama pembuat..." 
                               class="w-full rounded-xl border border-slate-300 bg-white py-2 pl-9 pr-3 text-xs placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ $dateFrom }}" 
                               title="Dari Tanggal"
                               class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <span class="text-xs text-slate-400">s/d</span>
                        <input type="date" 
                               name="date_to" 
                               value="{{ $dateTo }}" 
                               title="Sampai Tanggal"
                               class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <span>Filter</span>
                    </button>
                    @if ($search !== '' || $dateFrom !== '' || $dateTo !== '')
                    <a href="{{ route('quotations.index') }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Nomor Surat</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Kepada (Perusahaan / Klien)</th>
                        <th class="px-5 py-3.5">Daftar Item Barang</th>
                        <th class="px-5 py-3.5 text-right">Total Nilai</th>
                        <th class="px-5 py-3.5">Penandatangan</th>
                        <th class="px-5 py-3.5">Nama Pembuat</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($quotations as $q)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-3.5 font-bold text-indigo-700">
                            <a href="{{ route('quotations.show', $q) }}" class="hover:underline">
                                {{ $q->quotation_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                            {{ $q->formatted_date }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-800">{{ $q->customer_name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $q->customer_destination }}</div>
                        </td>
                        <td class="px-5 py-3.5 max-w-xs">
                            @php
                                $itemCount = is_array($q->items) ? count($q->items) : 0;
                                $firstItem = is_array($q->items) && isset($q->items[0]) ? $q->items[0]['description'] : '-';
                            @endphp
                            <div class="truncate font-medium text-slate-700" title="{{ $firstItem }}">{{ $firstItem }}</div>
                            @if ($itemCount > 1)
                                <span class="inline-block mt-0.5 text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.2 rounded">+ {{ $itemCount - 1 }} item lainnya</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap font-black text-slate-900">
                            {{ \App\Support\DataMasker::maskCurrency($q->total_amount) }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-semibold text-slate-700">{{ $q->signer_name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $q->signer_title }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="size-6 shrink-0 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-[10px]">
                                    {{ strtoupper(substr($q->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-xs truncate max-w-[130px]" title="{{ $q->user?->name ?? 'Sistem' }}">
                                        {{ $q->user?->name ?? 'Sistem' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[130px]">
                                        {{ $q->user?->role === 'admin' ? 'Superadmin' : ($q->user?->name ? 'User' : '-') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('quotations.show', $q) }}" 
                                   title="Lihat & Cetak" 
                                   class="rounded-lg p-1.5 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </a>

                                @if(!auth()->check() || !auth()->user()->isGuest())
                                <a href="{{ route('quotations.edit', $q) }}" 
                                   title="Edit Surat" 
                                   class="rounded-lg p-1.5 text-amber-600 hover:bg-amber-50 hover:text-amber-800 transition">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                </a>

                                <form method="POST" action="{{ route('quotations.duplicate', $q) }}" class="inline" onsubmit="return confirm('Duplikasi surat penawaran ini?')">
                                    @csrf
                                    <button type="submit" 
                                            title="Duplikasi Surat" 
                                            class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 hover:text-blue-800 transition cursor-pointer">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('quotations.destroy', $q) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat penawaran nomor {{ $q->quotation_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Hapus Surat" 
                                            class="rounded-lg p-1.5 text-rose-600 hover:bg-rose-50 hover:text-rose-800 transition cursor-pointer">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </div>
                            <h3 class="mt-2 text-sm font-bold text-slate-700">Belum ada surat penawaran</h3>
                            <p class="mt-1 text-xs text-slate-500">Mulai buat surat penawaran harga resmi pertama Anda.</p>
                            <div class="mt-4">
                                <a href="{{ route('quotations.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    <span>Buat Surat Penawaran Sekarang</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quotations->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $quotations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

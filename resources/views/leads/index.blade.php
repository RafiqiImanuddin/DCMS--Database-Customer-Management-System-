@extends('layouts.app')
@section('title', 'Leads Customer')
@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                <span class="size-2 rounded-full bg-brand-500"></span>
                Follow Up Hub
            </span>
            <span class="text-xs text-slate-400">&bull;</span>
            <span class="text-xs font-medium text-slate-500">Pelanggan Siap Follow Up</span>
        </div>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Leads Customer</h1>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <p class="hidden text-sm text-slate-500 md:block">{{ number_format($leads->total(), 0, ',', '.') }} lead</p>
        <a href="{{ route('broadcasts.templates.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Kelola Template
        </a>
        @if ($leads->total() <= 25000)
            <a href="{{ route('leads.download', request()->except('page')) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
                <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                Download Leads (Excel)
            </a>
        @else
            <details class="relative inline-block text-left">
                <summary class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-sm transition hover:bg-emerald-100 list-none">
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Download Leads ({{ number_format($leads->total(), 0, ',', '.') }} data)
                    <svg class="size-3.5 text-emerald-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                </summary>
                <div class="absolute right-0 z-30 mt-2 w-64 origin-top-right rounded-xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5 focus:outline-none">
                    <div class="px-2 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pilih Batch (Maks 25k data/file)</div>
                    @php
                        $totalBatches = (int) ceil($leads->total() / 25000);
                    @endphp
                    @for ($b = 1; $b <= $totalBatches; $b++)
                        @php
                            $start = ($b - 1) * 25000 + 1;
                            $end = min($b * 25000, $leads->total());
                        @endphp
                        <a href="{{ route('leads.download', array_merge(request()->except(['page', 'batch']), ['batch' => $b])) }}" class="flex items-center justify-between rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition">
                            <span class="flex items-center gap-2">
                                <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                                Batch {{ $b }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ number_format($start, 0, ',', '.') }} - {{ number_format($end, 0, ',', '.') }}</span>
                        </a>
                    @endfor
                </div>
            </details>
        @endif
    </div>
</div>

<form method="GET" class="dcms-card mb-5 grid gap-3 p-4 md:grid-cols-[minmax(220px,1fr)_160px_160px_auto]">
    <input name="search" value="{{ request('search') }}" placeholder="Cari pelanggan, item, atau nomor HP" class="dcms-input py-2.5 text-xs">
    <select name="classification" class="dcms-input py-2.5 text-xs">
        <option value="">Semua klasifikasi</option>
        @foreach(['tinggi', 'sedang', 'rendah', 'baru'] as $value)
            <option value="{{ $value }}" @selected(request('classification') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
    </select>
    <select name="phone_status" class="dcms-input py-2.5 text-xs">
        <option value="">Semua WhatsApp</option>
        <option value="valid" @selected(request('phone_status') === 'valid')>WhatsApp valid</option>
        <option value="invalid" @selected(request('phone_status') === 'invalid')>Nomor tidak valid</option>
        <option value="missing" @selected(request('phone_status') === 'missing')>Tanpa nomor</option>
    </select>
    <button class="dcms-primary-button px-5 text-xs font-bold">Filter</button>
</form>

<form method="POST" action="{{ route('broadcasts.bulk') }}" enctype="multipart/form-data" id="lead-broadcast-form">
    @csrf
    <input type="hidden" name="redirect_to" value="leads.index">
    <section class="dcms-card mb-5 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm font-black text-slate-900">Kirim Broadcast WhatsApp</h2>
                <p class="text-xs text-slate-500">Kirim pesan WhatsApp otomatis ke pelanggan terpilih melalui WatZap Gateway.</p>
            </div>
            <div class="flex items-center gap-2">
                @if(isset($templates) && $templates->isNotEmpty())
                    <select id="lead-template-select" class="dcms-input py-1.5 px-3 text-xs font-semibold w-auto border-brand-300 bg-brand-50/50 text-brand-900" onchange="applyLeadTemplate(this)">
                        <option value="">⚡ Gunakan Template Pesan...</option>
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" data-message="{{ $tmpl->message }}" data-image="{{ $tmpl->image_url ?? '' }}">{{ $tmpl->title }}</option>
                        @endforeach
                    </select>
                @endif
                <a href="{{ route('broadcasts.templates.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Kelola Template
                </a>
            </div>
        </div>

        <div class="mt-4 grid gap-5 lg:grid-cols-12">
            <!-- Kolom Editor Pesan & Lampiran (7 Kolom) -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="lead-bulk-message" class="text-xs font-bold uppercase tracking-wide text-slate-700">Isi Pesan Broadcast <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-slate-400">Maks. 2500 karakter</span>
                    </div>
                    <textarea id="lead-bulk-message" name="message" required maxlength="2500" rows="5" class="dcms-input text-xs leading-relaxed" placeholder="Halo Kak {nama}, terima kasih telah setia berbelanja di Gadgetmart. Kami punya penawaran spesial untuk produk {item}..."></textarea>
                    
                    <div class="flex flex-wrap items-center gap-1 text-[11px] text-slate-500">
                        <span class="font-bold text-slate-700">Variabel:</span>
                        <button type="button" onclick="insertLeadPlaceholder('{nama}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">{nama}</button>
                        <button type="button" onclick="insertLeadPlaceholder('{item}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">{item}</button>
                        <button type="button" onclick="insertLeadPlaceholder('{kode}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">{kode}</button>
                        <span class="text-slate-400 text-[10px] ml-1">(Otomatis diganti sesuai data pelanggan yang dipilih)</span>
                    </div>
                </div>

                <!-- Bagian Lampiran Gambar -->
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700">Lampirkan Gambar (Flyer / Promo)</label>
                        <span class="text-[10px] text-slate-400">JPG, PNG, WebP (Maks 5MB)</span>
                    </div>
                    <input type="file" id="lead-broadcast-image" name="image" accept="image/*" class="dcms-input text-xs" onchange="previewLeadUploadImage(this)">
                    <input type="hidden" id="lead-template-image-url" name="image_url" value="">

                    <div id="lead-image-filename-box" class="hidden flex items-center justify-between rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs text-emerald-800">
                        <span id="lead-image-filename-text" class="truncate font-semibold text-[11px]">Gambar terlampir</span>
                        <button type="button" onclick="removeLeadUploadImage()" class="text-[11px] font-bold text-rose-600 hover:underline ml-2 shrink-0">Hapus Gambar</button>
                    </div>
                </div>

                <!-- Tombol Eksekusi Broadcast -->
                <div>
                    <button id="lead-broadcast-button" type="submit" disabled class="dcms-primary-button w-full py-3 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50 shadow-md shadow-emerald-600/20">
                        🚀 Kirim Broadcast <span id="lead-selected-count">(0)</span> Lead Terpilih
                    </button>
                </div>
            </div>

            <!-- Kolom WhatsApp Live Message Preview (5 Kolom) -->
            <div class="lg:col-span-5 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700 flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Preview Pesan WhatsApp
                    </span>
                    <span id="preview-target-hint" class="text-[10px] font-bold text-emerald-800 bg-emerald-100/70 px-2.5 py-0.5 rounded-full truncate max-w-[170px]" title="Target pratinjau">
                        Contoh: Budi Santoso
                    </span>
                </div>

                <!-- Frame WhatsApp Chat Display -->
                <div class="flex-1 rounded-2xl border border-slate-300/80 bg-[#efeae2] p-3 shadow-inner flex flex-col justify-end min-h-[300px] relative overflow-hidden" style="background-image: radial-gradient(#d5cdc1 1px, transparent 1px); background-size: 14px 14px;">
                    <!-- WhatsApp Top Header Mockup -->
                    <div class="absolute inset-x-0 top-0 bg-[#075e54] px-3.5 py-2 text-white flex items-center gap-2.5 shadow-sm">
                        <div class="size-7 rounded-full bg-emerald-100 text-emerald-900 font-bold grid place-items-center text-[10px]">
                            GM
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold leading-tight truncate">Gadgetmart CS</div>
                            <div class="text-[9px] text-emerald-200 leading-tight">Online &bull; Terverifikasi</div>
                        </div>
                        <span class="text-[9px] bg-emerald-800/80 px-2 py-0.5 rounded font-mono text-emerald-100">WatZap</span>
                    </div>

                    <!-- Date Separator -->
                    <div class="text-center my-2 pt-8">
                        <span class="rounded-md bg-white/90 backdrop-blur px-2 py-0.5 text-[9px] font-semibold text-slate-500 shadow-xs uppercase tracking-wider">
                            Hari ini
                        </span>
                    </div>

                    <!-- Sent WhatsApp Message Bubble -->
                    <div class="self-end max-w-[92%] rounded-2xl rounded-tr-xs bg-[#d9fdd3] text-slate-900 shadow-sm border border-emerald-200/50 p-2.5 text-xs transition-all">
                        <!-- Attached Image Preview in Bubble -->
                        <div id="live-bubble-image-container" class="hidden mb-2 overflow-hidden rounded-xl bg-slate-200 border border-emerald-300/40">
                            <img id="live-bubble-image" src="#" alt="Lampiran Flyer" class="max-h-44 w-full object-cover">
                        </div>

                        <!-- Dynamic Text Preview with Placeholder Replacements -->
                        <div id="live-bubble-text" class="whitespace-pre-wrap leading-relaxed text-xs break-words text-slate-800 font-sans">
                            Halo Kak Budi Santoso, terima kasih telah berbelanja di Gadgetmart...
                        </div>

                        <!-- Bubble Footer: Timestamp & Blue Double Check -->
                        <div class="mt-1.5 flex items-center justify-end gap-1 text-[9px] text-slate-400">
                            <span id="live-bubble-time">16:00</span>
                            <svg class="size-3.5 text-sky-500 inline-block" viewBox="0 0 16 15" fill="none">
                                <path d="M15.01 3.316l-7.79 7.79-4.22-4.22.94-.94 3.28 3.28 6.85-6.85.94.94zm-3.79 0l-7.79 7.79-1.37-1.37.94-.94.43.43 6.85-6.85.94.94z" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabel Data Leads Customer -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/90 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5"><input id="select-all-leads" type="checkbox" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Pilih semua lead"></th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-3 py-3.5">No. HP Konsumen</th>
                        <th class="px-3 py-3.5">Item</th>
                        <th class="px-3 py-3.5">Klasifikasi</th>
                        <th class="px-3 py-3.5">Ditetapkan</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($leads as $lead)
                    <tr class="border-b border-slate-100 align-middle transition hover:bg-brand-50/40">
                        <td class="px-5 py-4">
                            @if($lead->prediction)
                                @php
                                    $leadPhoneDigits = preg_replace('/[^0-9]/', '', (string) $lead->no_hp_customer) ?? '';
                                    $leadPhoneValid = str_starts_with($leadPhoneDigits, '62') && strlen($leadPhoneDigits) >= 10 && strlen($leadPhoneDigits) <= 15;
                                @endphp
                                <input type="checkbox" name="prediction_ids[]" value="{{ $lead->prediction->id }}"
                                    class="lead-checkbox size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    data-customer-name="{{ $lead->customer_vendor_name ?: 'Kaka' }}"
                                    data-item-name="{{ $lead->dscription ?: ($lead->item_code ?: 'produk') }}"
                                    data-customer-code="{{ $lead->customer_vendor_code }}"
                                    aria-label="Pilih {{ $lead->customer_vendor_name ?: 'Kaka' }}"
                                    @disabled(! $leadPhoneValid)>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $lead->customer_vendor_name ?: 'Kaka' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $lead->customer_vendor_code }}</div>
                        </td>
                        <td class="px-3 py-4">
                            @if($lead->no_hp_customer)
                                @php
                                    $leadPhoneDigits = preg_replace('/[^0-9]/', '', (string) $lead->no_hp_customer) ?? '';
                                    $leadPhoneValid = str_starts_with($leadPhoneDigits, '62') && strlen($leadPhoneDigits) >= 10 && strlen($leadPhoneDigits) <= 15;
                                @endphp
                                @if($leadPhoneValid)
                                    <a href="https://wa.me/{{ $leadPhoneDigits }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:text-emerald-800">{{ $lead->no_hp_customer }}</a>
                                    <div class="mt-1 text-[11px] font-bold text-emerald-600">WhatsApp valid</div>
                                @else
                                    <span class="font-semibold text-amber-700">{{ $lead->no_hp_customer }}</span>
                                    <div class="mt-1 text-[11px] font-bold text-amber-600">Nomor tidak valid</div>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">No HP belum tersedia</span>
                                <div class="mt-1 text-[11px] font-bold text-slate-500">Tanpa nomor</div>
                            @endif
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-medium text-slate-800">{{ $lead->item_code ?: '-' }}</span>
                                @if($lead->category === 'LAINNYA')
                                    <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">Lainnya</span>
                                @else
                                    <span class="inline-flex items-center rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-700">{{ $lead->category }}</span>
                                @endif
                            </div>
                            <div class="mt-1 max-w-52 truncate text-xs text-slate-500">{{ $lead->dscription ?: '-' }}</div>
                        </td>
                        <td class="px-3 py-4"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold capitalize text-slate-700">{{ $lead->classification }}</span></td>
                        <td class="px-3 py-4 whitespace-nowrap text-slate-600">{{ $lead->assigned_at?->format('d M Y H:i') ?: '-' }}</td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Follow Up</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada Leads Customer.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</form>
<div class="mt-4">{{ $leads->links() }}</div>

<script>
function updateLeadMessagePreview() {
    const textarea = document.getElementById('lead-bulk-message');
    const bubbleText = document.getElementById('live-bubble-text');
    const targetHint = document.getElementById('preview-target-hint');
    const timeEl = document.getElementById('live-bubble-time');

    if (!bubbleText) return;

    // Get active sample or checked customer data
    const checked = document.querySelector('.lead-checkbox:checked');
    let sampleName = 'Budi Santoso';
    let sampleItem = 'Samsung Galaxy A55 5G';
    let sampleCode = 'C100001';

    if (checked) {
        sampleName = checked.getAttribute('data-customer-name') || sampleName;
        sampleItem = checked.getAttribute('data-item-name') || sampleItem;
        sampleCode = checked.getAttribute('data-customer-code') || sampleCode;
        if (targetHint) {
            targetHint.textContent = `Target: ${sampleName}`;
        }
    } else {
        if (targetHint) {
            targetHint.textContent = `Contoh: ${sampleName}`;
        }
    }

    let raw = textarea && textarea.value ? textarea.value : 'Halo Kak {nama}, kami punya promo khusus untuk produk {item}...';

    // Replace placeholders dynamically
    let rendered = raw
        .replaceAll('{nama}', sampleName)
        .replaceAll('{nama_pelanggan}', sampleName)
        .replaceAll('{item}', sampleItem)
        .replaceAll('{kode}', sampleCode);

    bubbleText.textContent = rendered;

    // Update time
    if (timeEl) {
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        timeEl.textContent = `${hrs}:${mins}`;
    }
}

function applyLeadTemplate(select) {
    const selectedOption = select.options[select.selectedIndex];
    const message = selectedOption.getAttribute('data-message') || '';
    const image = selectedOption.getAttribute('data-image') || '';

    const textarea = document.getElementById('lead-bulk-message');
    if (textarea && message) {
        textarea.value = message;
    }

    const imageUrlInput = document.getElementById('lead-template-image-url');
    const bubbleImgContainer = document.getElementById('live-bubble-image-container');
    const bubbleImg = document.getElementById('live-bubble-image');
    const filenameBox = document.getElementById('lead-image-filename-box');
    const filenameText = document.getElementById('lead-image-filename-text');

    if (image && imageUrlInput && bubbleImgContainer && bubbleImg) {
        imageUrlInput.value = image;
        bubbleImg.src = image;
        bubbleImgContainer.classList.remove('hidden');

        if (filenameBox && filenameText) {
            filenameText.textContent = 'Gambar dari Template';
            filenameBox.classList.remove('hidden');
        }
    }

    updateLeadMessagePreview();
}

function insertLeadPlaceholder(placeholder) {
    const textarea = document.getElementById('lead-bulk-message');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
    updateLeadMessagePreview();
}

function previewLeadUploadImage(input) {
    const bubbleImgContainer = document.getElementById('live-bubble-image-container');
    const bubbleImg = document.getElementById('live-bubble-image');
    const imageUrlInput = document.getElementById('lead-template-image-url');
    const filenameBox = document.getElementById('lead-image-filename-box');
    const filenameText = document.getElementById('lead-image-filename-text');

    if (imageUrlInput) imageUrlInput.value = '';

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            bubbleImg.src = e.target.result;
            bubbleImgContainer.classList.remove('hidden');
            if (filenameBox && filenameText) {
                filenameText.textContent = `${file.name} (${(file.size / 1024).toFixed(0)} KB)`;
                filenameBox.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    } else {
        bubbleImgContainer.classList.add('hidden');
        if (filenameBox) filenameBox.classList.add('hidden');
    }
}

function removeLeadUploadImage() {
    const input = document.getElementById('lead-broadcast-image');
    const bubbleImgContainer = document.getElementById('live-bubble-image-container');
    const bubbleImg = document.getElementById('live-bubble-image');
    const imageUrlInput = document.getElementById('lead-template-image-url');
    const filenameBox = document.getElementById('lead-image-filename-box');

    if (input) input.value = '';
    if (bubbleImg) bubbleImg.src = '#';
    if (imageUrlInput) imageUrlInput.value = '';
    if (bubbleImgContainer) bubbleImgContainer.classList.add('hidden');
    if (filenameBox) filenameBox.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.getElementById('lead-bulk-message');
    if (textarea) {
        textarea.addEventListener('input', updateLeadMessagePreview);
        textarea.addEventListener('change', updateLeadMessagePreview);
    }

    document.querySelectorAll('.lead-checkbox').forEach(cb => {
        cb.addEventListener('change', updateLeadMessagePreview);
    });

    const selectAll = document.getElementById('select-all-leads');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            setTimeout(updateLeadMessagePreview, 60);
        });
    }

    updateLeadMessagePreview();
});
</script>
@endsection

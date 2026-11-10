@extends('layouts.app')

@section('title', ($isEdit ? 'Edit Surat Penawaran' : 'Buat Surat Penawaran') . ' - Dashboard Manager')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Title -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('quotations.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition">Surat Penawaran</a>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-600">{{ $isEdit ? 'Edit Surat' : 'Surat Baru' }}</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">
                {{ $isEdit ? 'Edit Surat Penawaran: ' . $quotation->quotation_number : 'Buat Surat Penawaran Harga Baru' }}
            </h1>
            <p class="text-xs text-slate-500 sm:text-sm">Isi formulir di sebelah kiri dan lihat hasil pratinjau lembar surat secara langsung di sebelah kanan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('quotations.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                <span>Kembali</span>
            </a>
            <button type="button" onclick="printQuotationLetter()" class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3.5 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                <span>Cetak / PDF</span>
            </button>
            <button type="button" onclick="downloadQuotationImage()" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                <span>Unduh Gambar</span>
            </button>
        </div>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
        <div class="font-bold">Mohon perbaiki isian form berikut:</div>
        <ul class="mt-1 list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Main Grid: Form Editor (Left) & Live Preview (Right) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        <!-- LEFT: FORM EDITOR (5 COLS) -->
        <div class="lg:col-span-5 space-y-5">
            <form id="quotationForm" method="POST" action="{{ $isEdit ? route('quotations.update', $quotation) : route('quotations.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <!-- Card 1: Data Pokok Surat -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="flex size-5 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-black text-indigo-700">1</span>
                        Informasi Dokumen & Tujuan
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="quotation_number" 
                                   id="inputQuotationNumber" 
                                   value="{{ old('quotation_number', $quotation->quotation_number) }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono font-bold text-indigo-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-rose-500">*</span></label>
                            <input type="date" 
                                   name="letter_date" 
                                   id="inputLetterDate" 
                                   value="{{ old('letter_date', $quotation->letter_date ? $quotation->letter_date->format('Y-m-d') : date('Y-m-d')) }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kota Asal <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="letter_city" 
                                   id="inputLetterCity" 
                                   value="{{ old('letter_city', $quotation->letter_city ?? 'Banjarmasin') }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tujuan / Lokasi <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="customer_destination" 
                                   id="inputCustomerDestination" 
                                   value="{{ old('customer_destination', $quotation->customer_destination ?? 'di-tempat') }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kepada Yth: (Nama Perusahaan / PT / Instansi) <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="customer_name" 
                               id="inputCustomerName" 
                               value="{{ old('customer_name', $quotation->customer_name ?? 'PT. ') }}" 
                               placeholder="Contoh: PT. Maju Bersama Makmur" 
                               required 
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Card 2: Daftar Barang Penawaran -->
                <div class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <span class="flex size-5 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-black text-indigo-700">2</span>
                            Rincian Barang Penawaran
                        </h3>
                        <button type="button" onclick="addNewItemRow()" class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition cursor-pointer">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>+ Tambah Baris</span>
                        </button>
                    </div>

                    <!-- Autocomplete Helper from Pricelist -->
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3">
                        <label class="block text-[11px] font-bold text-indigo-900 mb-1 flex items-center gap-1.5">
                            <svg class="size-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            Cepat: Pilih / Salin dari Pricelist Gadgetmart
                        </label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   id="pricelistQuickSearch" 
                                   placeholder="Ketik nama gadget (cth: iPhone, Samsung, iPad)..." 
                                   list="pricelistDatalist" 
                                   class="flex-1 rounded-lg border border-indigo-200 bg-white px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <datalist id="pricelistDatalist">
                                @foreach ($pricelistProducts as $p)
                                    <option value="{{ $p['name'] }}" data-price="{{ $p['price'] }}">{{ $p['name'] }} - Rp {{ number_format($p['price'], 0, ',', '.') }}</option>
                                @endforeach
                            </datalist>
                            <button type="button" onclick="applyPricelistSelection()" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition cursor-pointer">
                                Masukkan
                            </button>
                        </div>
                    </div>

                    <!-- Item Rows Container -->
                    <div id="itemsContainer" class="space-y-3">
                        @php
                            $currentItems = old('items', $quotation->items ?? []);
                            if (empty($currentItems)) {
                                $currentItems = [['description' => '', 'price' => 0, 'qty' => 1, 'total' => 0]];
                            }
                        @endphp

                        @foreach ($currentItems as $idx => $item)
                        <div class="item-row rounded-xl border border-slate-200 bg-slate-50/60 p-3 space-y-2 relative" data-index="{{ $idx }}">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-600">Barang #<span class="row-number">{{ $idx + 1 }}</span></span>
                                <button type="button" onclick="removeItemRow(this)" class="text-slate-400 hover:text-rose-600 transition" title="Hapus baris ini">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </div>

                            <div>
                                <input type="text" 
                                       name="items[{{ $idx }}][description]" 
                                       value="{{ $item['description'] ?? '' }}" 
                                       placeholder="Deskripsi barang / tipe gadget / spesifikasi..." 
                                       required 
                                       oninput="syncPreview()" 
                                       class="item-description w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 font-medium focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-12 gap-2">
                                <div class="col-span-6">
                                    <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Harga Satuan (Rp)</label>
                                    <input type="number" 
                                           name="items[{{ $idx }}][price]" 
                                           value="{{ $item['price'] ?? 0 }}" 
                                           min="0" 
                                           step="1000" 
                                           required 
                                           oninput="calcRow(this)" 
                                           class="item-price w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 font-mono font-bold focus:border-indigo-500 focus:outline-none">
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Qty</label>
                                    <input type="number" 
                                           name="items[{{ $idx }}][qty]" 
                                           value="{{ $item['qty'] ?? 1 }}" 
                                           min="1" 
                                           required 
                                           oninput="calcRow(this)" 
                                           class="item-qty w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-center font-bold text-slate-800 focus:border-indigo-500 focus:outline-none">
                                </div>

                                <div class="col-span-4">
                                    <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Total (Rp)</label>
                                    <div class="item-total-display rounded-lg bg-slate-200/70 px-2 py-1.5 text-xs font-mono font-black text-slate-800 text-right">
                                        Rp {{ number_format(((int)($item['price'] ?? 0)) * max(1, (int)($item['qty'] ?? 1)), 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Diskon & Total Rekap Section -->
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-600">Subtotal Barang:</span>
                            <span id="subtotalDisplay" class="font-mono font-bold text-slate-800">Rp 0</span>
                        </div>

                        <div class="space-y-1.5 border-t border-slate-200/80 pt-2.5">
                            <div class="flex items-center justify-between">
                                <label for="inputDiscount" class="block text-xs font-semibold text-slate-700">
                                    Potongan Harga / Diskon (Rp)
                                </label>
                                <span class="text-[10px] text-slate-400">Kosongkan jika tidak ada diskon</span>
                            </div>
                            <div class="relative flex items-center">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 font-mono">Rp</span>
                                <input type="number" 
                                       name="discount" 
                                       id="inputDiscount" 
                                       value="{{ old('discount', ($quotation->discount && $quotation->discount > 0) ? $quotation->discount : '') }}" 
                                       min="0" 
                                       step="1000" 
                                       placeholder="0" 
                                       oninput="syncPreview()" 
                                       class="w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3 py-2 text-xs font-mono font-bold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-2.5 text-right">
                            <span class="text-xs font-bold text-slate-700">Total Penawaran Akhir:</span>
                            <div id="grandTotalDisplay" class="text-base sm:text-lg font-black text-indigo-700 font-mono">Rp 0</div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Redaksi Teks Surat & Penandatangan -->
                <div class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="flex size-5 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-black text-indigo-700">3</span>
                        Redaksi & Tanda Tangan
                    </h3>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Pembuka Surat</label>
                        <textarea name="greeting_text" 
                                  id="inputGreetingText" 
                                  rows="3" 
                                  oninput="syncPreview()" 
                                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 leading-relaxed">{{ old('greeting_text', $quotation->greeting_text) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Bawah Tabel)</label>
                        <input type="text" 
                               name="notes" 
                               id="inputNotes" 
                               value="{{ old('notes', $quotation->notes ?? '*Harga yang tertera dapat berubah sewaktu-waktu') }}" 
                               oninput="syncPreview()" 
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Penutup Surat</label>
                        <input type="text" 
                               name="closing_text" 
                               id="inputClosingText" 
                               value="{{ old('closing_text', $quotation->closing_text ?? 'Demikian penawaran yang kami sampaikan, atas perhatiannya kami ucapkan terima kasih.') }}" 
                               oninput="syncPreview()" 
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penandatangan <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="signer_name" 
                                   id="inputSignerName" 
                                   value="{{ old('signer_name', $quotation->signer_name ?? 'RAFIQI IMANUDDIN') }}" 
                                   required 
                                   oninput="syncPreview()" 
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-slate-900 focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Posisi <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="signer_title" 
                                   id="inputSignerTitle" 
                                   value="{{ old('signer_title', $quotation->signer_title ?? 'Marketing Gadget Mart') }}" 
                                   required 
                                   oninput="syncPreview()" 
                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700">Upload File Tanda Tangan (Opsional)</label>
                            <span class="text-[10px] text-slate-400">JPG, PNG, JPEG</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="file" 
                                   name="signature_file" 
                                   id="inputSignatureFile" 
                                   accept="image/*" 
                                   class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <button type="button" 
                                    id="btnResetSignature" 
                                    onclick="clearSignatureToPlain()" 
                                    class="shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                                    title="Hapus gambar & jadikan ruang tanda tangan polosan">
                                Jadikan Polos
                            </button>
                            <input type="hidden" name="reset_signature_to_default" id="inputResetSignature" value="0">
                            <input type="hidden" name="signature_base64" id="inputSignatureBase64" value="">
                            <input type="hidden" name="signature_scale" id="inputSignatureScale" value="{{ old('signature_scale', $quotation->signature_scale ?? 100) }}">
                        </div>

                        <!-- Image Toolbar: Rotate, Scale & Remove Background (Transparan) -->
                        <div id="signatureToolsPanel" class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 space-y-3 transition-all {{ ($quotation->signature_url ?? false) ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-indigo-900 flex items-center gap-1.5">
                                    <svg class="size-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/></svg>
                                    Editor Tanda Tangan
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <span id="rotationIndicator" class="text-[10px] font-mono font-bold text-indigo-700 bg-indigo-100 px-1.5 py-0.5 rounded" title="Sudut Rotasi">0°</span>
                                    <span id="scaleIndicatorBadge" class="text-[10px] font-mono font-bold text-slate-700 bg-white border border-slate-200 px-1.5 py-0.5 rounded" title="Skala Ukuran">100%</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Putar 90 Derajat -->
                                <button type="button" 
                                        id="btnRotateSignature" 
                                        onclick="rotateSignature(90)" 
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs">
                                    <svg class="size-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    <span>Putar 90°</span>
                                </button>

                                <!-- Hapus Background / Transparan Toggle -->
                                <button type="button" 
                                        id="btnToggleTransparent" 
                                        onclick="toggleSignatureTransparency()" 
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition cursor-pointer shadow-2xs">
                                    <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                    <span id="labelTransparent">✨ Hapus Background</span>
                                </button>

                                <!-- Tombol Cepat Perbesar & Perkecil -->
                                <div class="inline-flex rounded-lg border border-slate-200 bg-white shadow-2xs">
                                    <button type="button" 
                                            onclick="stepSignatureScale(-10)" 
                                            class="px-2 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition border-r border-slate-200 cursor-pointer" 
                                            title="Perkecil Ukuran (-10%)">
                                        🔍- Perkecil
                                    </button>
                                    <button type="button" 
                                            onclick="stepSignatureScale(10)" 
                                            class="px-2 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer" 
                                            title="Perbesar Ukuran (+10%)">
                                        🔍+ Perbesar
                                    </button>
                                </div>

                                <!-- Reset Asli -->
                                <button type="button" 
                                        onclick="resetSignatureEdits()" 
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-slate-800 transition ml-auto cursor-pointer">
                                    <span>↺ Reset Asli</span>
                                </button>
                            </div>

                            <!-- Slider Ukuran Tanda Tangan (Perbesar / Perkecil Presisi) -->
                            <div class="pt-2 border-t border-indigo-100/80">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-slate-600 font-medium">Ukuran Tanda Tangan:</span>
                                    <span id="scaleDisplay" class="font-mono font-bold text-indigo-700">100%</span>
                                </div>
                                <input type="range" 
                                       id="signatureScaleRange" 
                                       min="40" 
                                       max="200" 
                                       step="5" 
                                       value="{{ old('signature_scale', $quotation->signature_scale ?? 100) }}" 
                                       oninput="onSignatureScaleChange(this.value)" 
                                       class="w-full accent-indigo-600 cursor-pointer">
                                <div class="flex justify-between text-[9px] text-slate-400 mt-0.5 font-mono">
                                    <span>Kecil (40%)</span>
                                    <span>Normal (100%)</span>
                                    <span>Besar (200%)</span>
                                </div>
                            </div>

                            <!-- Slider Sensitivitas Transparan (Tampil saat transparan aktif) -->
                            <div id="transparentSliderWrapper" class="hidden pt-2 border-t border-indigo-100/80">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-slate-600 font-medium">Sensitivitas Transparan Kertas:</span>
                                    <span id="toleranceDisplay" class="font-mono font-bold text-indigo-700">75%</span>
                                </div>
                                <input type="range" 
                                       id="signatureTolerance" 
                                       min="30" 
                                       max="95" 
                                       value="75" 
                                       oninput="onSignatureToleranceChange(this.value)" 
                                       class="w-full accent-indigo-600 cursor-pointer">
                                <span class="text-[10px] text-slate-400 block mt-0.5">Geser jika masih ada bayangan kertas atau jika tulisan tanda tangan terpotong.</span>
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400">Biarkan kosong untuk kanvas polosan (ruang tanda tangan bersih tanpa gambar).</p>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('quotations.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/30 hover:bg-indigo-700 transition cursor-pointer">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Surat Penawaran' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT: LIVE REAL-TIME PREVIEW OF A4 PAPER LETTER (7 COLS) -->
        <div class="lg:col-span-7 sticky top-4">
            <div class="rounded-2xl border border-slate-300/80 bg-slate-200/50 p-4 sm:p-6 shadow-inner">
                <div class="flex items-center justify-between mb-3 text-xs text-slate-600">
                    <div class="flex items-center gap-2 font-bold uppercase tracking-wider text-slate-500">
                        <span class="inline-block size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Pratinjau Lembar Surat A4 (Real-Time)</span>
                    </div>
                    <div class="text-[11px] text-slate-500">Standar Format Gadget Mart</div>
                </div>

                <!-- WHITE LETTERHEAD PAPER CONTAINER (EXACT MATCH TO TEMPLATE) -->
                <div id="printableLetterPaper" class="mx-auto bg-white p-8 sm:p-12 shadow-xl border border-slate-200/60 transition-all font-sans text-black relative" style="max-width: 650px; min-height: 840px;">
                    <!-- HEADER: LOGO KIRI & PENAWARAN HARGA MERAH KANAN -->
                    <div class="flex items-start justify-between pb-4">
                        <div>
                            <img src="{{ asset('images/gadgetmart-surat-logo.png') }}" 
                                 alt="Gadget Mart" 
                                 class="h-14 sm:h-16 w-auto object-contain"
                                 onerror="this.src='{{ asset('workspace-sosmed/logo.png') }}'">
                        </div>
                        <div class="text-right">
                            <h1 class="text-xl sm:text-2xl font-black tracking-wide text-red-600 leading-none">PENAWARAN</h1>
                            <h1 class="text-xl sm:text-2xl font-black tracking-wide text-red-600 leading-tight">HARGA</h1>
                        </div>
                    </div>

                    <!-- RECIPIENT BLOCK -->
                    <div class="mt-6 text-xs sm:text-sm text-black leading-snug">
                        <p class="text-slate-800">Kepada Yth:</p>
                        <p id="prevCustomerName" class="font-bold text-black mt-0.5">PT. </p>
                        <p id="prevCustomerDest" class="font-bold text-black">di-tempat</p>
                    </div>

                    <!-- GREETING / OPENING -->
                    <div class="mt-6 text-xs sm:text-sm text-black leading-relaxed">
                        <p class="font-normal mb-3">Dengan hormat,</p>
                        <p id="prevGreetingText" class="font-normal text-justify">
                            Gadget Mart merupakan store retail handphone terkemuka dan terbesar di Kota Banjarmasin yang bergerak di bidang penjualan Handphone, Aksesoris, Laptop. Kami ingin memberikan penawaran harga barang dengan rincian sebagai berikut:
                        </p>
                    </div>

                    <!-- TABLE: DESKRIPSI | HARGA | QTY | TOTAL -->
                    <div class="mt-4 mb-3 overflow-hidden">
                        <table class="w-full border-collapse border border-black text-xs">
                            <thead>
                                <tr style="background-color: #d9e2ec;" class="border border-black">
                                    <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 52%;">DESKRIPSI</th>
                                    <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 22%;">HARGA</th>
                                    <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 8%;">QTY</th>
                                    <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 18%;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody id="prevTableBody">
                                <!-- Dynamic rows injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- NOTES & CLOSING -->
                    <div class="mt-2 text-xs sm:text-sm text-black leading-relaxed">
                        <p id="prevNotes" class="italic text-[11px] sm:text-xs text-black mb-3">
                            *Harga yang tertera dapat berubah sewaktu-waktu
                        </p>
                        <p id="prevClosingText" class="text-black mb-8">
                            Demikian penawaran yang kami sampaikan, atas perhatiannya kami ucapkan terima kasih.
                        </p>
                    </div>

                    <!-- SIGNATURE BLOCK (BOTTOM RIGHT) -->
                    <div class="mt-8 ml-auto text-center text-xs sm:text-sm text-black" style="width: 240px;">
                        <p id="prevDateCity" class="mb-2 text-black">Banjarmasin, {{ date('d F Y') }}</p>
                        
                        <!-- Ruang tanda tangan polosan (bersih tanpa watermark/gambar kecuali diunggah) -->
                        <div id="prevSignatureWrapper" class="min-h-24 flex items-center justify-center my-1 relative">
                            <img id="prevSignatureImg" 
                                 src="{{ $quotation->signature_url ?? '' }}" 
                                 alt="Tanda Tangan" 
                                 class="w-auto object-contain mx-auto"
                                 style="{{ $quotation->signature_url ? 'display: block;' : 'display: none;' }} height: {{ round(80 * (($quotation->signature_scale ?? 100) / 100)) }}px;">
                        </div>

                        <p id="prevSignerName" class="font-bold underline text-sm sm:text-base text-black mt-2">RAFIQI IMANUDDIN</p>
                        <p id="prevSignerTitle" class="font-bold text-xs sm:text-sm text-black mt-0.5">Marketing Gadget Mart</p>
                    </div>
                </div>

                <!-- Footer Helper -->
                <div class="mt-3 text-center text-[11px] text-slate-500">
                    💡 Gunakan tombol <strong>Cetak / PDF</strong> di atas untuk mencetak atau menyimpan dokumen sebagai file PDF siap pakai.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Sembunyikan elemen dashboard luar saat dialog cetak / simpan PDF dibuka */
    aside,
    header,
    #sidebar,
    #sidebar-overlay,
    .lg\:col-span-5,
    .flex.items-center.gap-2,
    button,
    a,
    .text-slate-500 {
        display: none !important;
    }

    body, main, .space-y-6, .grid {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }

    .min-h-screen {
        min-height: auto !important;
    }

    .lg\:col-span-7 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .lg\:col-span-7 > div {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    .lg\:col-span-7 > div > div:first-child,
    .lg\:col-span-7 > div > div:last-child {
        display: none !important;
    }

    #printableLetterPaper {
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 10mm 15mm !important;
        min-height: auto !important;
        display: block !important;
    }

    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }
}
</style>

<!-- html2canvas for instant Image Download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Quotation Real-Time Editor & Synchronizer Script -->
<script src="{{ asset('js/quotation-editor.js') }}?v={{ time() }}"></script>
@endsection

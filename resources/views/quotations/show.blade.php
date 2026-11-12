@extends('layouts.app')

@section('title', 'Surat Penawaran ' . $quotation->quotation_number . ' - Dashboard Manager')

@section('content')
<div class="space-y-6">
    <!-- Action Bar (Hidden when printed) -->
    <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('quotations.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition">Surat Penawaran</a>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-700">{{ $quotation->quotation_number }}</span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">
                {{ $quotation->customer_name }}
            </h1>
            <p class="text-xs text-slate-500">Dibuat pada {{ $quotation->formatted_date }} oleh {{ $quotation->user?->name ?? 'Admin' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('quotations.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                <span>Kembali</span>
            </a>

            @if(!\App\Support\DataMasker::isGuest())
            <a href="{{ route('quotations.edit', $quotation) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100 transition">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                <span>Edit Surat</span>
            </a>
            @endif

            <button type="button" onclick="downloadLetterImage()" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                <span>Unduh Gambar</span>
            </button>

            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Alert Success -->
    @if (session('success'))
    <div class="no-print flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- A4 PAPER VIEW (EXACT TEMPLATE) -->
    <div class="flex justify-center p-2 sm:p-6 bg-slate-100 rounded-2xl print:p-0 print:bg-white print:m-0">
        <div id="quotationSheet" class="bg-white p-8 sm:p-14 shadow-2xl border border-slate-200/80 print:shadow-none print:border-none w-full font-sans text-black relative" style="max-width: 700px; min-height: 900px;">
            <!-- HEADER: LOGO KIRI & PENAWARAN HARGA MERAH KANAN -->
            <div class="flex items-start justify-between pb-4">
                <div>
                    <img src="{{ asset('images/gadgetmart-surat-logo.png') }}" 
                         alt="Gadget Mart" 
                         class="h-16 w-auto object-contain"
                         onerror="this.src='{{ asset('workspace-sosmed/logo.png') }}'">
                </div>
                <div class="text-right">
                    <h1 class="text-2xl font-black tracking-wide text-red-600 leading-none">PENAWARAN</h1>
                    <h1 class="text-2xl font-black tracking-wide text-red-600 leading-tight">HARGA</h1>
                </div>
            </div>

            <!-- RECIPIENT BLOCK -->
            <div class="mt-6 text-sm text-black leading-snug">
                <p>Kepada Yth:</p>
                <p class="font-bold text-black mt-0.5">{{ $quotation->customer_name }}</p>
                <p class="font-bold text-black">{{ $quotation->customer_destination }}</p>
            </div>

            <!-- GREETING / OPENING -->
            <div class="mt-6 text-sm text-black leading-relaxed">
                <p class="font-normal mb-3">Dengan hormat,</p>
                <p class="font-normal text-justify">
                    {{ $quotation->greeting_text ?: 'Gadget Mart merupakan store retail handphone terkemuka dan terbesar di Kota Banjarmasin yang bergerak di bidang penjualan Handphone, Aksesoris, Laptop. Kami ingin memberikan penawaran harga barang dengan rincian sebagai berikut:' }}
                </p>
            </div>

            <!-- TABLE: DESKRIPSI | HARGA | QTY | TOTAL -->
            <div class="mt-5 mb-3 overflow-hidden">
                <table class="w-full border-collapse border border-black text-xs">
                    <thead>
                        <tr style="background-color: #d9e2ec;" class="border border-black">
                            <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 52%;">DESKRIPSI</th>
                            <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 22%;">HARGA</th>
                            <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 8%;">QTY</th>
                            <th class="border border-black px-3 py-2 text-center font-bold text-black tracking-wider uppercase" style="width: 18%;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotation->items ?? [] as $item)
                        <tr class="border border-black">
                            <td class="border border-black px-3 py-2.5 text-left font-normal text-black leading-snug">
                                {{ $item['description'] ?? '-' }}
                            </td>
                            <td class="border border-black px-3 py-2.5 text-center font-normal text-black whitespace-nowrap">
                                {{ \App\Support\DataMasker::maskCurrency($item['price'] ?? 0) }}
                            </td>
                            <td class="border border-black px-3 py-2.5 text-center font-normal text-black">
                                {{ $item['qty'] ?? 1 }}
                            </td>
                            <td class="border border-black px-3 py-2.5 text-center font-normal text-black whitespace-nowrap">
                                {{ \App\Support\DataMasker::maskCurrency($item['total'] ?? (($item['price'] ?? 0) * ($item['qty'] ?? 1))) }}
                            </td>
                        </tr>
                        @empty
                        <tr class="border border-black">
                            <td class="border border-black px-3 py-3 text-center text-slate-400" colspan="4">Tidak ada barang</td>
                        </tr>
                        @endforelse

                        @if (($quotation->discount ?? 0) > 0)
                        <tr class="border border-black font-semibold bg-slate-50">
                            <td class="border border-black px-3 py-1.5 text-right uppercase text-[11px]" colspan="3">SUBTOTAL:</td>
                            <td class="border border-black px-3 py-1.5 text-center whitespace-nowrap text-[11px] font-mono">
                                {{ \App\Support\DataMasker::maskCurrency($quotation->subtotal) }}
                            </td>
                        </tr>
                        <tr class="border border-black font-semibold text-rose-600 bg-rose-50/40">
                            <td class="border border-black px-3 py-1.5 text-right uppercase text-[11px]" colspan="3">DISKON:</td>
                            <td class="border border-black px-3 py-1.5 text-center whitespace-nowrap text-[11px] font-mono">
                                {{ \App\Support\DataMasker::isGuest() ? 'Rp ***.***' : '- Rp. ' . number_format($quotation->discount, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="border border-black font-bold bg-slate-100 text-black">
                            <td class="border border-black px-3 py-2 text-right uppercase text-xs font-bold" colspan="3">TOTAL AKHIR:</td>
                            <td class="border border-black px-3 py-2 text-center whitespace-nowrap text-xs font-mono font-black">
                                {{ \App\Support\DataMasker::maskCurrency($quotation->total_amount) }}
                            </td>
                        </tr>
                        @elseif (is_array($quotation->items) && count($quotation->items) > 1)
                        <tr class="border border-black bg-slate-50 font-bold">
                            <td class="border border-black px-3 py-2 text-right uppercase text-xs" colspan="3">TOTAL:</td>
                            <td class="border border-black px-3 py-2 text-center whitespace-nowrap text-xs font-mono font-black">
                                {{ \App\Support\DataMasker::maskCurrency($quotation->total_amount) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- NOTES & CLOSING -->
            <div class="mt-2 text-sm text-black leading-relaxed">
                <p class="italic text-xs text-black mb-3">
                    {{ $quotation->notes ?: '*Harga yang tertera dapat berubah sewaktu-waktu' }}
                </p>
                <p class="text-black mb-8">
                    {{ $quotation->closing_text ?: 'Demikian penawaran yang kami sampaikan, atas perhatiannya kami ucapkan terima kasih.' }}
                </p>
            </div>

            <!-- SIGNATURE BLOCK (BOTTOM RIGHT) -->
            <div class="mt-8 ml-auto text-center text-sm text-black" style="width: 250px;">
                <p class="mb-2 text-black">{{ $quotation->letter_city ?? 'Banjarmasin' }}, {{ $quotation->formatted_date }}</p>
                
                <div class="min-h-24 flex items-center justify-center my-1 relative">
                    @if ($quotation->signature_url)
                        <img src="{{ $quotation->signature_url }}" 
                             alt="Tanda Tangan" 
                             style="height: {{ round(80 * (($quotation->signature_scale ?? 100) / 100)) }}px;"
                             class="w-auto object-contain mx-auto">
                    @else
                        <!-- Polosan: Ruang kosong bersih untuk tanda tangan -->
                        <div class="h-20"></div>
                    @endif
                </div>

                <p class="font-bold underline text-base text-black mt-2">{{ $quotation->signer_name }}</p>
                <p class="font-bold text-sm text-black mt-0.5">{{ $quotation->signer_title }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Print Stylesheet -->
<style>
@media print {
    /* Hide all page layouts, sidebar, headers */
    header, aside, nav, .no-print, #sidebar, #sidebar-overlay {
        display: none !important;
    }
    body, main {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    @page {
        size: A4 portrait;
        margin: 15mm;
    }
    #quotationSheet {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<!-- html2canvas for instant Image Download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function downloadLetterImage() {
    const sheet = document.getElementById('quotationSheet');
    if (!sheet || typeof html2canvas === 'undefined') {
        alert('Fitur download gambar sedang dimuat, silakan coba lagi.');
        return;
    }

    const number = "{{ str_replace(['/', '\\', ':'], '_', $quotation->quotation_number) }}";

    html2canvas(sheet, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff'
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = `${number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }).catch(err => {
        console.error('Canvas error:', err);
        alert('Gagal mengunduh gambar surat.');
    });
}
</script>
@endsection

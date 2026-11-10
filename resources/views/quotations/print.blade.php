<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penawaran - {{ $quotation->quotation_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #fff;
            color: #000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>
</head>
<body class="p-6">
    <div style="max-width: 650px; margin: 0 auto;">
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
                            Rp. {{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="border border-black px-3 py-2.5 text-center font-normal text-black">
                            {{ $item['qty'] ?? 1 }}
                        </td>
                        <td class="border border-black px-3 py-2.5 text-center font-normal text-black whitespace-nowrap">
                            Rp. {{ number_format($item['total'] ?? (($item['price'] ?? 0) * ($item['qty'] ?? 1)), 0, ',', '.') }}
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
                            Rp. {{ number_format($quotation->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="border border-black font-semibold text-rose-600 bg-rose-50/40">
                        <td class="border border-black px-3 py-1.5 text-right uppercase text-[11px]" colspan="3">DISKON:</td>
                        <td class="border border-black px-3 py-1.5 text-center whitespace-nowrap text-[11px] font-mono">
                            - Rp. {{ number_format($quotation->discount, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="border border-black font-bold bg-slate-100 text-black">
                        <td class="border border-black px-3 py-2 text-right uppercase text-xs font-bold" colspan="3">TOTAL AKHIR:</td>
                        <td class="border border-black px-3 py-2 text-center whitespace-nowrap text-xs font-mono font-black">
                            Rp. {{ number_format($quotation->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @elseif (is_array($quotation->items) && count($quotation->items) > 1)
                    <tr class="border border-black bg-slate-50 font-bold">
                        <td class="border border-black px-3 py-2 text-right uppercase text-xs" colspan="3">TOTAL:</td>
                        <td class="border border-black px-3 py-2 text-center whitespace-nowrap text-xs font-mono font-black">
                            Rp. {{ number_format($quotation->total_amount, 0, ',', '.') }}
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

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

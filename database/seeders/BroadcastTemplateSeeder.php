<?php

namespace Database\Seeders;

use App\Models\BroadcastTemplate;
use Illuminate\Database\Seeder;

class BroadcastTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'title' => 'Follow Up Repeat Order (Jadwal Beli Kembali)',
                'message' => "Halo Kak {nama}! Terima kasih telah setia bersama Gadgetmart. Berdasarkan riwayat belanja, kemungkinan saatnya Kakak memperbarui kebutuhan seputar {item}. Dapatkan promo tukar tambah menarik & garansi resmi hanya di Gadgetmart. Balas pesan ini untuk info promo hari ini ya! 📱✨",
            ],
            [
                'title' => 'Promo Khusus Aksesoris & Proteksi (ACC)',
                'message' => "Halo Kak {nama}, gimana kabar gadget-nya? Supaya perangkat Kakak selalu terlindungi, Gadgetmart ada promo diskon spesial untuk tempered glass, case original, adaptor fast charging, & audio. Yuk mampir ke toko atau balas pesan ini untuk pilihan katalognya! 🎧⚡",
            ],
            [
                'title' => 'Greeting & Cek Kepuasan Konsumen',
                'message' => "Halo Kak {nama}, salam hangat dari tim Gadgetmart! Kami ingin memastikan apakah perangkat atau belanjaan Kakak berfungsi dengan nyaman. Jika ada pertanyaan, servis, atau kebutuhan aksesoris, tim kami siap membantu sepenuh hati. Have a wonderful day! 😊🙌",
            ],
            [
                'title' => 'Voucher Eksklusif Pelanggan Setia (VIP)',
                'message' => "Spesial untuk Kak {nama}! Sebagai apresiasi pelanggan setia Gadgetmart, kami siapkan voucher cashback belanja untuk transaksi berikutnya. Tunjukkan pesan WhatsApp resmi ini ke kasir store Gadgetmart terdekat untuk klaim keuntungannya ya Kak! 🎁🛍️",
            ],
        ];

        foreach ($templates as $tpl) {
            BroadcastTemplate::firstOrCreate(
                ['title' => $tpl['title']],
                ['message' => $tpl['message']]
            );
        }
    }
}


<?php

namespace App\Services;

use App\Models\BroadcastReport;
use App\Models\LeadCustomer;
use App\Models\RepeatOrderPrediction;
use App\Models\User;
use App\Models\WhatsAppChat;
use App\Models\WhatsAppMessage;

class WhatsAppChatService
{
    public function __construct(
        protected WatzapService $watzap
    ) {}

    public function seedDefaultChatsIfEmpty(): void
    {
        if (WhatsAppChat::exists()) {
            return;
        }

        $defaultChats = [
            [
                'name' => 'AmelZahraAzka 💐',
                'phone' => '628976483883',
                'cabang' => 'TANPA CABANG',
                'folder' => 'all',
                'format_type' => 'Keep',
                'barang_keep' => 'Infinix XPAD 20 PRO 8/256GB',
                'notes' => 'Tanya cicilan & baterai Infinix XPAD. Tertarik tenor 12 bulan.',
                'tags' => ['potensi', 'infinix', 'cicilan'],
                'last_message' => 'Nanti kita cek sama-sama yaa, soalnya bisa beda beda tiap akun ka^^',
                'last_message_at' => now()->subMinutes(5),
                'unread_count' => 0,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'Mau tanya yg aku tandai itu betrai nya brapa dan cicilin nya brap2',
                        'media_url' => '/images/sample-flyer.jpg',
                        'media_type' => 'image',
                        'created_at' => now()->subHours(2),
                    ],
                    [
                        'sender_type' => 'agent',
                        'agent_name' => 'zofia',
                        'message' => 'Besar cicilannya fleksibel banget ya Kak, tergantung produk, pilihan tenor, dan metode bayar yang Kakak mau! Infoin dulu aja produk impian Kakak ke Mimin, nanti Mimin carikan estimasi angsuran yang paling ramah di dompet! ✨',
                        'created_at' => now()->subHours(1)->addMinutes(15),
                    ],
                    [
                        'sender_type' => 'agent',
                        'agent_name' => 'zofia',
                        'message' => 'tenornya fleksibel yaa ka, bisa disesuiain sama kebutuhan kaka 🥰 Mulai dari 1 bulan - 12 bulan. Kalau produknya di atas 10 jutaan, kaka juga bisa ambil tenor 18 bulan atau bahkan 24 bulan. Nanti kita cek sama-sama yaa, soalnya bisa beda beda tiap akun ka^^',
                        'created_at' => now()->subMinutes(5),
                    ],
                ],
            ],
            [
                'name' => 'putri',
                'phone' => '6281255443322',
                'cabang' => 'GM AMT',
                'folder' => 'unresponded',
                'format_type' => 'Tanya Promo',
                'barang_keep' => 'Samsung Galaxy A55 5G',
                'notes' => 'Menanyakan perbedaan harga online dan offline',
                'tags' => ['samsung', 'unresponded'],
                'last_message' => 'kalo dionline sama offline biasanya beda kah kak harganya?',
                'last_message_at' => now()->subMinutes(12),
                'unread_count' => 2,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'Halo min mau tanya',
                        'created_at' => now()->subMinutes(15),
                    ],
                    [
                        'sender_type' => 'customer',
                        'message' => 'kalo dionline sama offline biasanya beda kah kak harganya?',
                        'created_at' => now()->subMinutes(12),
                    ],
                ],
            ],
            [
                'name' => 'Zidanr',
                'phone' => '6285211998877',
                'cabang' => 'GM TJG',
                'folder' => 'all',
                'format_type' => 'Follow Up',
                'barang_keep' => 'Xiaomi Redmi Note 13',
                'notes' => 'Menunggu kabar stok warna Midnight Black',
                'tags' => ['xiaomi', 'ready stock'],
                'last_message' => 'gimna ka unitnya sudah ready?',
                'last_message_at' => now()->subMinutes(20),
                'unread_count' => 2,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'gimna ka unitnya sudah ready?',
                        'created_at' => now()->subMinutes(20),
                    ],
                ],
            ],
            [
                'name' => 'adha aulia akbar',
                'phone' => '6287899001122',
                'cabang' => 'GM BJB',
                'folder' => 'all',
                'format_type' => 'Keep',
                'barang_keep' => 'iPhone 15 128GB Pink',
                'notes' => 'Domisili Banjarmasin, mau ambil di cabang Banjarbaru',
                'tags' => ['iphone', 'potensi tinggi'],
                'last_message' => 'domisili dari Banjarmasin ka, bisa ambil di cabang bjb?',
                'last_message_at' => now()->subMinutes(25),
                'unread_count' => 1,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'domisili dari Banjarmasin ka, bisa ambil di cabang bjb?',
                        'created_at' => now()->subMinutes(25),
                    ],
                ],
            ],
            [
                'name' => 'saiapuddin',
                'phone' => '6289655443311',
                'cabang' => 'GM BTC',
                'folder' => 'all',
                'format_type' => 'Tanya Promo',
                'barang_keep' => 'Vivo V30 5G',
                'notes' => 'Tanya promo trade-in hp lama',
                'tags' => ['vivo', 'trade in'],
                'last_message' => 'banjarmasin kalimantan selatan ada promo trade in kah?',
                'last_message_at' => now()->subMinutes(40),
                'unread_count' => 1,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'banjarmasin kalimantan selatan ada promo trade in kah?',
                        'created_at' => now()->subMinutes(40),
                    ],
                ],
            ],
            [
                'name' => 'Ultramen',
                'phone' => '6281344556677',
                'cabang' => 'GM KPS',
                'folder' => 'all',
                'format_type' => 'Booking',
                'barang_keep' => 'Oppo Reno 11 Pro',
                'notes' => 'Lokasi Kandangan kota',
                'tags' => ['oppo', 'booking'],
                'last_message' => 'Kandangan kota ka, bisa dikirim hari ini?',
                'last_message_at' => now()->subHours(1),
                'unread_count' => 1,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'Kandangan kota ka, bisa dikirim hari ini?',
                        'created_at' => now()->subHours(1),
                    ],
                ],
            ],
            [
                'name' => 'Andry a',
                'phone' => '6282133445566',
                'cabang' => 'GM SDN',
                'folder' => 'all',
                'format_type' => 'Cicilan',
                'barang_keep' => 'Realme 12 Pro+ 5G',
                'notes' => 'Mau kredit tanpa DP',
                'tags' => ['realme', 'kredit'],
                'last_message' => 'Mau kredit bleh ngga persyaratannya apa aja min',
                'last_message_at' => now()->subHours(2),
                'unread_count' => 0,
                'messages' => [
                    [
                        'sender_type' => 'customer',
                        'message' => 'Mau kredit bleh ngga persyaratannya apa aja min',
                        'created_at' => now()->subHours(2),
                    ],
                ],
            ],
        ];

        foreach ($defaultChats as $data) {
            $chat = WhatsAppChat::create([
                'customer_name' => $data['name'],
                'phone_number' => $data['phone'],
                'cabang' => $data['cabang'],
                'folder' => $data['folder'],
                'format_type' => $data['format_type'],
                'barang_keep' => $data['barang_keep'],
                'notes' => $data['notes'],
                'tags_json' => json_encode($data['tags']),
                'last_message' => $data['last_message'],
                'last_message_at' => $data['last_message_at'],
                'unread_count' => $data['unread_count'],
            ]);

            foreach ($data['messages'] as $msg) {
                WhatsAppMessage::create([
                    'whatsapp_chat_id' => $chat->id,
                    'sender_type' => $msg['sender_type'],
                    'agent_name' => $msg['agent_name'] ?? null,
                    'message' => $msg['message'],
                    'media_url' => $msg['media_url'] ?? null,
                    'media_type' => $msg['media_type'] ?? null,
                    'status' => 'sent',
                    'created_at' => $msg['created_at'],
                ]);
            }
        }
    }

    public function getFolderCounts(?User $user = null): array
    {
        $all = WhatsAppChat::count();
        $unresponded = WhatsAppChat::where('folder', 'unresponded')->orWhere('unread_count', '>', 0)->count();
        $myChats = $user ? WhatsAppChat::where('assigned_user_id', $user->id)->count() : 0;
        $unassigned = WhatsAppChat::whereNull('assigned_user_id')->count();
        $resolved = WhatsAppChat::where('folder', 'resolved')->count();

        return [
            'all' => max(396, $all),
            'my_chats' => $myChats,
            'unassigned' => $unassigned,
            'unresponded' => max(112, $unresponded),
            'resolved' => max(1251, $resolved),
        ];
    }

    public function getGroups(): array
    {
        return [
            ['name' => 'GM AMT', 'badge' => 104],
            ['name' => 'GM TJG', 'badge' => 78],
            ['name' => 'GM KPS', 'badge' => 49],
            ['name' => 'GM SDN (SUNGAI DANAU)', 'badge' => 8],
            ['name' => 'GM BTC', 'badge' => 64],
            ['name' => 'COD ACC GADGETMART', 'badge' => 12],
            ['name' => 'Hasil Tim FU FX', 'badge' => 67],
            ['name' => 'GM BJB', 'badge' => 34],
        ];
    }

    public function sendMessage(WhatsAppChat $chat, string $message, ?User $user = null): WhatsAppMessage
    {
        $agentName = $user ? $user->name : 'Staff';

        $msg = WhatsAppMessage::create([
            'whatsapp_chat_id' => $chat->id,
            'sender_type' => 'agent',
            'user_id' => $user?->id,
            'agent_name' => $agentName,
            'message' => $message,
            'status' => 'sent',
        ]);

        $chat->update([
            'last_message' => $message,
            'last_message_at' => now(),
            'unread_count' => 0,
            'folder' => 'all',
        ]);

        // Send via WatZap REST API if configured
        if ($this->watzap->isConfigured()) {
            $res = $this->watzap->sendMessage($chat->phone_number, $message);
            $msg->update([
                'status' => $res['success'] ? 'sent' : 'failed',
            ]);

            BroadcastReport::create([
                'user_id' => $user ? $user->id : 1,
                'customer_vendor_code' => $chat->customer_vendor_code,
                'phone_number' => $chat->phone_number,
                'channel' => 'whatsapp',
                'message' => $message,
                'status' => $res['success'] ? 'sent' : 'failed',
                'provider_response' => $res['message'].($res['raw'] ? ' | '.$res['raw'] : ''),
                'sent_at' => $res['success'] ? now() : null,
            ]);
        }

        return $msg;
    }

    public function saveCrmData(WhatsAppChat $chat, array $data, ?User $user = null): void
    {
        $chat->update([
            'format_type' => $data['format_type'] ?? $chat->format_type,
            'cabang' => $data['cabang'] ?? $chat->cabang,
            'customer_name' => $data['customer_name'] ?? $chat->customer_name,
            'phone_number' => $data['phone_number'] ?? $chat->phone_number,
            'barang_keep' => $data['barang_keep'] ?? $chat->barang_keep,
            'tanggal_pengambilan' => ! empty($data['tanggal_pengambilan']) ? $data['tanggal_pengambilan'] : null,
            'notes' => $data['notes'] ?? $chat->notes,
            'assigned_user_id' => $user?->id ?? $chat->assigned_user_id,
        ]);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $chat->tags = $data['tags'];
            $chat->save();
        }

        // Also sync/create into LeadCustomer if requested
        if ($user && ! empty($data['barang_keep'])) {
            LeadCustomer::updateOrCreate([
                'user_id' => $user->id,
                'customer_vendor_code' => $chat->customer_vendor_code ?: 'CRM-'.$chat->id,
            ], [
                'customer_vendor_name' => $chat->customer_name,
                'no_hp_customer' => $chat->phone_number,
                'classification' => 'tinggi',
                'item_code' => $chat->barang_keep,
                'dscription' => $chat->notes ?: 'Tersimpan dari WhatsApp CRM',
                'status' => 'follow_up',
                'assigned_at' => now(),
            ]);
        }
    }
}

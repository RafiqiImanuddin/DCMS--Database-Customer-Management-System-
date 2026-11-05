<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppChat;
use App\Models\WhatsAppMessage;
use App\Services\WatzapService;
use App\Services\WhatsAppChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function index(Request $request, WhatsAppChatService $chatService, WatzapService $watzap): View
    {
        $chatService->seedDefaultChatsIfEmpty();

        $user = $request->user();
        $folder = $request->query('folder', 'all');
        $search = $request->query('search');
        $tagFilter = $request->query('tag');
        $groupFilter = $request->query('group');

        $query = WhatsAppChat::query()->orderBy('last_message_at', 'desc');

        if ($groupFilter) {
            $query->where('cabang', $groupFilter);
        } elseif ($folder === 'my_chats') {
            $query->where('assigned_user_id', $user->id);
        } elseif ($folder === 'unassigned') {
            $query->whereNull('assigned_user_id');
        } elseif ($folder === 'unresponded') {
            $query->where(function ($q) {
                $q->where('folder', 'unresponded')
                    ->orWhere('unread_count', '>', 0);
            });
        } elseif ($folder === 'resolved') {
            $query->where('folder', 'resolved');
        }

        if ($search) {
            $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('customer_name', $likeOp, "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('last_message', $likeOp, "%{$search}%")
                    ->orWhere('barang_keep', $likeOp, "%{$search}%");
            });
        }

        if ($tagFilter) {
            $query->where('tags_json', 'like', "%{$tagFilter}%");
        }

        $chats = $query->get();

        $activeChatId = $request->query('chat_id');
        $activeChat = null;

        if ($activeChatId) {
            $activeChat = WhatsAppChat::with(['messages' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }])->find($activeChatId);
        }

        if (! $activeChat && $chats->isNotEmpty()) {
            $activeChat = WhatsAppChat::with(['messages' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }])->find($chats->first()->id);
        }

        if ($activeChat && $activeChat->unread_count > 0) {
            $activeChat->update(['unread_count' => 0]);
        }

        $folderCounts = $chatService->getFolderCounts($user);
        $groups = $chatService->getGroups();

        return view('whatsapp.index', [
            'chats' => $chats,
            'activeChat' => $activeChat,
            'folder' => $folder,
            'search' => $search,
            'tagFilter' => $tagFilter,
            'groupFilter' => $groupFilter,
            'folderCounts' => $folderCounts,
            'groups' => $groups,
            'isConfigured' => $watzap->isConfigured(),
            'isSuperAdmin' => $user->isSuperAdmin(),
            'currentUser' => $user,
        ]);
    }

    public function sendMessage(Request $request, WhatsAppChat $chat, WhatsAppChatService $chatService): JsonResponse|RedirectResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $msg = $chatService->sendMessage($chat, $request->input('message'), $request->user());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('whatsapp.index', [
            'chat_id' => $chat->id,
            'folder' => $request->query('folder', 'all'),
        ]);
    }

    public function saveCrm(Request $request, WhatsAppChat $chat, WhatsAppChatService $chatService): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'format_type' => 'nullable|string|max:50',
            'cabang' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'barang_keep' => 'nullable|string|max:255',
            'tanggal_pengambilan' => 'nullable|date',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'send_confirmation' => 'nullable|boolean',
        ]);

        $chatService->saveCrmData($chat, $data, $request->user());

        // Jika tombol 'Simpan dan Kirim' ditekan dan ada barang keep
        if ($request->input('action') === 'save_and_send' || $request->boolean('send_confirmation')) {
            $tgl = ! empty($data['tanggal_pengambilan']) ? date('d-m-Y', strtotime($data['tanggal_pengambilan'])) : '-';
            $confirmMsg = "Halo kak {$chat->customer_name}, terima kasih sudah menghubungi Gadgetmart! 📱✨\n\nData Keep Anda sudah tercatat:\n📌 Format: ".($data['format_type'] ?? 'Keep')."\n🏬 Cabang: ".($data['cabang'] ?? 'Pusat')."\n📦 Barang: ".($data['barang_keep'] ?? '-')."\n📅 Tgl Pengambilan: {$tgl}\n\nSilakan konfirmasi saat pengambilan ya kak. Terima kasih! 🙏";

            $chatService->sendMessage($chat, $confirmMsg, $request->user());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data CRM berhasil diperbarui.',
            ]);
        }

        return redirect()->route('whatsapp.index', [
            'chat_id' => $chat->id,
            'folder' => $request->query('folder', 'all'),
        ])->with('success', 'Data CRM berhasil disimpan!');
    }

    public function startChat(Request $request, WhatsAppChatService $chatService): RedirectResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'item_code' => 'nullable|string|max:255',
            'customer_vendor_code' => 'nullable|string|max:50',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->input('phone_number'));
        if (str_starts_with($phone, '08')) {
            $phone = '628'.substr($phone, 2);
        }

        $chat = WhatsAppChat::firstOrCreate(
            ['phone_number' => $phone],
            [
                'customer_name' => $request->input('customer_name'),
                'customer_vendor_code' => $request->input('customer_vendor_code'),
                'cabang' => 'TANPA CABANG',
                'folder' => 'all',
                'format_type' => 'Follow Up',
                'barang_keep' => $request->input('item_code'),
                'last_message' => 'Chat baru dimulai oleh '.$request->user()->name,
                'last_message_at' => now(),
                'unread_count' => 0,
            ]
        );

        return redirect()->route('whatsapp.index', ['chat_id' => $chat->id]);
    }

    public function resolve(WhatsAppChat $chat): RedirectResponse
    {
        $chat->update(['folder' => 'resolved']);

        return redirect()->route('whatsapp.index', ['chat_id' => $chat->id])
            ->with('success', 'Chat telah ditandai selesai (Resolved).');
    }

    public function block(WhatsAppChat $chat): RedirectResponse
    {
        $tags = $chat->tags;
        if (! in_array('blocked', $tags)) {
            $tags[] = 'blocked';
        }
        $chat->tags = $tags;
        $chat->save();

        return redirect()->route('whatsapp.index', ['chat_id' => $chat->id])
            ->with('success', 'Customer telah diblokir.');
    }
}

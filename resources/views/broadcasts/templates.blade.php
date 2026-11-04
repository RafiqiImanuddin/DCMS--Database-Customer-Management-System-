@extends('layouts.app')
@section('title', 'Template Broadcast WhatsApp')
@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                WhatsApp Broadcast Hub
            </span>
            <span class="text-xs text-slate-400">&bull;</span>
            <span class="text-xs font-medium text-slate-500">Template Pesan & Lampiran Gambar</span>
        </div>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Kelola Template Broadcast</h1>
        <p class="mt-1 text-xs text-slate-500">Buat dan kelola template pesan otomatis dengan dukungan variabel dinamis dan lampiran gambar.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('broadcasts.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>
            Laporan Broadcast
        </a>
        <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-brand-500">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Kirim Broadcast di Leads
        </a>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Form Tambah Template Baru -->
    <div class="lg:col-span-1">
        <div class="dcms-card p-5 sticky top-24">
            <h2 class="text-base font-black text-slate-900">Tambah Template Baru</h2>
            <p class="mt-1 text-xs text-slate-500">Simpan format pesan promosi atau follow up repeat order.</p>

            <form method="POST" action="{{ route('broadcasts.templates.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Judul Template <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required maxlength="150" placeholder="Contoh: Promo Repeat Order Gadget" class="dcms-input text-xs">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Isi Pesan WhatsApp <span class="text-rose-500">*</span></label>
                    </div>
                    <textarea id="template-message-input" name="message" required rows="5" maxlength="2500" class="dcms-input text-xs leading-relaxed" placeholder="Halo Kak {nama}, kami punya penawaran spesial untuk produk {item}..."></textarea>
                    
                    <!-- Variabel Dinamis Helper -->
                    <div class="mt-2 rounded-lg bg-slate-50 border border-slate-200/80 p-2 text-[11px]">
                        <span class="font-bold text-slate-700">Variabel Dinamis (Klik untuk sisipkan):</span>
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            <button type="button" onclick="insertPlaceholder('{nama}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">{nama}</button>
                            <button type="button" onclick="insertPlaceholder('{item}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">{item}</button>
                            <button type="button" onclick="insertPlaceholder('{kode}')" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-brand-700 hover:bg-brand-100 transition">{kode}</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Lampiran Gambar (Opsional)</label>
                    <p class="mb-2 text-[11px] text-slate-500">Pilih file gambar flyer, katalog, atau promo (JPG, PNG, WebP maks 5MB).</p>
                    <input type="file" id="template-image-file" name="image" accept="image/*" class="dcms-input text-xs" onchange="previewTemplateImage(this)">
                    
                    <div id="image-preview-container" class="mt-2 hidden rounded-xl border border-slate-200 bg-slate-50 p-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold text-slate-600">Pratinjau Gambar</span>
                            <button type="button" onclick="clearTemplateImage()" class="text-[10px] font-bold text-rose-600 hover:underline">Hapus</button>
                        </div>
                        <img id="image-preview" src="#" alt="Pratinjau" class="max-h-40 w-auto rounded-lg object-contain mx-auto shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-700">Atau URL Gambar Publik</label>
                    <input type="url" name="image_url" placeholder="https://example.com/promo.jpg" class="dcms-input text-xs">
                </div>

                <button type="submit" class="dcms-primary-button w-full py-2.5 text-xs font-bold">
                    Simpan Template
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Template yang Tersedia -->
    <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-900">Daftar Template Broadcast ({{ $templates->count() }})</h2>
            <span class="text-xs text-slate-500">Siap digunakan saat mengirim broadcast</span>
        </div>

        @forelse($templates as $template)
            <div class="dcms-card p-5 transition hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">{{ $template->title }}</h3>
                            @if($template->image_url)
                                <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    Ada Gambar
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 rounded-xl bg-slate-50 p-3.5 border border-slate-100 text-xs leading-relaxed text-slate-700 whitespace-pre-wrap font-sans">
{{ $template->message }}
                        </div>
                    </div>

                    @if($template->image_url)
                        <div class="shrink-0 sm:w-28 text-center">
                            <a href="{{ $template->image_url }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-xl border border-slate-200 bg-white p-1 hover:border-brand-500 transition">
                                <img src="{{ $template->image_url }}" alt="{{ $template->title }}" class="h-20 w-28 rounded-lg object-cover transition group-hover:scale-105">
                                <span class="mt-1 block text-[10px] font-bold text-brand-600 group-hover:underline">Buka Gambar</span>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>Diperbarui: {{ $template->updated_at->format('d M Y H:i') }}</span>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('broadcasts.templates.destroy', $template) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmForm(this, { title: 'Hapus Template Pesan?', message: 'Template pesan ini akan dihapus secara permanen dari sistem.', itemName: '{{ addslashes($template->title) }}' })" class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-100 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="dcms-card p-12 text-center text-slate-500">
                <svg class="mx-auto size-12 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <div class="mt-2 text-sm font-bold text-slate-700">Belum ada template broadcast</div>
                <p class="text-xs text-slate-400 mt-1">Gunakan formulir di sebelah kiri untuk membuat template pesan WhatsApp pertama Anda.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('template-message-input');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
}

function previewTemplateImage(input) {
    const container = document.getElementById('image-preview-container');
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.classList.add('hidden');
    }
}

function clearTemplateImage() {
    const input = document.getElementById('template-image-file');
    const container = document.getElementById('image-preview-container');
    const preview = document.getElementById('image-preview');
    if (input) input.value = '';
    if (preview) preview.src = '#';
    if (container) container.classList.add('hidden');
}
</script>
@endsection


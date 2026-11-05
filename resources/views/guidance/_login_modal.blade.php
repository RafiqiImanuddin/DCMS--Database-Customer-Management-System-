@php
    $modalUser = auth()->user();
    $modalIsSuperAdmin = $modalUser && $modalUser->isSuperAdmin();
@endphp

<div id="login-guidance-modal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6 overflow-y-auto" role="dialog" aria-modal="true">
    <!-- Backdrop Blur -->
    <div id="login-guidance-backdrop" onclick="dismissLoginGuidance()" class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity duration-300 opacity-100"></div>

    <!-- Modal Panel -->
    <div id="login-guidance-panel" class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-300 scale-100 opacity-100 border border-slate-100 my-8">
        
        <!-- Header Banner -->
        <div class="relative px-6 py-6 {{ $modalIsSuperAdmin ? 'bg-gradient-to-r from-slate-950 via-purple-950 to-indigo-950 text-white' : 'bg-gradient-to-r from-slate-950 via-sky-950 to-blue-950 text-white' }}">
            <button type="button" onclick="dismissLoginGuidance()" class="absolute right-4 top-4 rounded-xl p-1.5 text-white/60 hover:bg-white/10 hover:text-white transition" aria-label="Tutup">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $modalIsSuperAdmin ? 'bg-purple-500/20 text-purple-300 border border-purple-400/30' : 'bg-sky-500/20 text-sky-300 border border-sky-400/30' }} px-3 py-1 text-[11px] font-bold tracking-wide uppercase">
                    @if ($modalIsSuperAdmin)
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        Superadmin Guidance &amp; Formula
                    @else
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        Panduan Harian Staf Sales
                    @endif
                </span>
                <span class="text-xs text-white/50">• Setiap Kali Login</span>
            </div>

            <h2 class="mt-2.5 text-xl font-black tracking-tight text-white sm:text-2xl">
                @if ($modalIsSuperAdmin)
                    Ringkasan Rumus &amp; Algoritma DCMS
                @else
                    SOP Layanan Prima Sales Konsumen
                @endif
            </h2>
            <p class="mt-1 text-xs text-slate-300 leading-relaxed sm:text-sm">
                @if ($modalIsSuperAdmin)
                    Selamat datang, <span class="font-bold text-amber-300">{{ $modalUser->name ?? 'Superadmin' }}</span>! Berikut ringkasan formula penentuan potensi repeat order dan standar evaluasi kinerja sales harian.
                @else
                    Selamat bertugas, <span class="font-bold text-sky-300">{{ $modalUser->name ?? 'Admin Sales' }}</span>! Terapkan 4 pilar standar operasional berikut agar pelayanan konsumen Gadgetmart selalu maksimal.
                @endif
            </p>
        </div>

        <!-- Body Content -->
        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
            @if ($modalIsSuperAdmin)
                <!-- SUPERADMIN CONTENT: FORMULAS & ALGORITHMS -->
                <!-- Card 1: Rumus Potensi Repeat Order -->
                <div class="rounded-2xl border border-purple-100 bg-purple-50/50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-purple-600 text-white font-black text-xs shadow-sm">
                            f(x)
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-sm font-black text-slate-900">1. Rumus Penentuan User Potensi (Repeat Order)</h4>
                                <span class="rounded-md bg-purple-200/80 px-2 py-0.5 text-[10px] font-bold text-purple-900">Algoritma Median Gap</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                                Tanggal prediksi beli dihitung dari selisih hari historis (<code class="bg-purple-100 px-1 py-0.5 rounded text-purple-800 text-[11px] font-mono">Gaps = [d₂-d₁, d₃-d₂,...]</code>) menggunakan nilai tengah:
                            </p>
                            <div class="mt-2 rounded-xl bg-white p-2.5 border border-purple-200/60 font-mono text-xs text-purple-950 font-bold">
                                Predicted Date = Last Order Date + Median Gap Days
                            </div>
                            <div class="mt-2.5 grid grid-cols-2 gap-2 text-[11px]">
                                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-2 text-emerald-900">
                                    <span class="font-bold block">🟢 Potensi Tinggi:</span>
                                    Selisih hari prediksi &le; 30 hari (Jendela emas beli).
                                </div>
                                <div class="rounded-lg bg-amber-50 border border-amber-200 p-2 text-amber-900">
                                    <span class="font-bold block">🟡 Potensi Sedang:</span>
                                    Selisih hari prediksi 31 &ndash; 60 hari.
                                </div>
                                <div class="rounded-lg bg-slate-100 border border-slate-200 p-2 text-slate-800">
                                    <span class="font-bold block">⚪ Potensi Rendah:</span>
                                    Selisih hari prediksi &gt; 60 hari.
                                </div>
                                <div class="rounded-lg bg-blue-50 border border-blue-200 p-2 text-blue-900">
                                    <span class="font-bold block">🔵 Pelanggan Baru:</span>
                                    1 transaksi (Belum ada gap historis).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Rumus Penilaian Kinerja Sales Harian -->
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-indigo-600 text-white font-black text-xs shadow-sm">
                            ∑
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-sm font-black text-slate-900">2. Rumus Evaluasi KPI Sales Harian</h4>
                                <span class="rounded-md bg-indigo-200/80 px-2 py-0.5 text-[10px] font-bold text-indigo-900">Skor Terbobot 100%</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                                Evaluasi harian menggunakan akumulasi skor terbobot 4 kriteria utama:
                            </p>
                            <div class="mt-2 rounded-xl bg-white p-2.5 border border-indigo-200/60 font-mono text-xs text-indigo-950 font-bold">
                                Total Skor = (Chat &times; 25%) + (Respon &times; 25%) + (Etika &times; 25%) + (Promo &times; 25%)
                            </div>
                            <div class="mt-2.5 flex items-center justify-between text-center gap-1.5 text-[11px]">
                                <div class="flex-1 rounded-lg bg-emerald-50 border border-emerald-200 p-1.5 font-bold text-emerald-800">Grade A<br><span class="text-[10px] font-normal">&ge; 90.0</span></div>
                                <div class="flex-1 rounded-lg bg-blue-50 border border-blue-200 p-1.5 font-bold text-blue-800">Grade B<br><span class="text-[10px] font-normal">80.0 &ndash; 89.9</span></div>
                                <div class="flex-1 rounded-lg bg-amber-50 border border-amber-200 p-1.5 font-bold text-amber-800">Grade C<br><span class="text-[10px] font-normal">70.0 &ndash; 79.9</span></div>
                                <div class="flex-1 rounded-lg bg-rose-50 border border-rose-200 p-1.5 font-bold text-rose-800">Grade D<br><span class="text-[10px] font-normal">&lt; 70.0</span></div>
                            </div>
                            <p class="mt-2 text-[11px] text-slate-500 italic">
                                *Catatan: Hak input dan ubah nilai hanya dimiliki oleh Superadmin (Manager). Akun staf hanya melihat hasil secara read-only.
                            </p>
                        </div>
                    </div>
                </div>

            @else
                <!-- STAFF ADMIN CONTENT: GENERAL SOP & GUIDANCE (NO FORMULAS) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- SOP 1: Balas Chat -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-sky-300 transition">
                        <div class="flex items-center gap-2.5 text-sky-600 font-bold text-xs mb-1.5">
                            <span class="grid size-6 place-items-center rounded-lg bg-sky-100 text-sky-700 text-xs font-black">1</span>
                            <span>Cara Balas Chat Konsumen</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Gunakan salam hangat, sebut nama pelanggan, to-the-point menjawab pertanyaan, dan akhiri dengan penawaran bantuan lanjutan yang ramah.
                        </p>
                    </div>

                    <!-- SOP 2: Respon Time -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-emerald-300 transition">
                        <div class="flex items-center gap-2.5 text-emerald-600 font-bold text-xs mb-1.5">
                            <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-emerald-700 text-xs font-black">2</span>
                            <span>Kecepatan Respon (&lt; 3 Menit)</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Standby penuh di perangkat Anda selama jam kerja. Tanggapi chat baru maksimal 3 menit agar pelanggan tidak berpindah ke toko lain.
                        </p>
                    </div>

                    <!-- SOP 3: Etika & 3S -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-amber-300 transition">
                        <div class="flex items-center gap-2.5 text-amber-600 font-bold text-xs mb-1.5">
                            <span class="grid size-6 place-items-center rounded-lg bg-amber-100 text-amber-700 text-xs font-black">3</span>
                            <span>Etika Pelayanan & 3S</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Terapkan Senyum, Salam, Sapa. Dilarang berdebat saat ada komplain, utamakan empati dan dengarkan kebutuhan pelanggan terlebih dahulu.
                        </p>
                    </div>

                    <!-- SOP 4: Promo & Upselling -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-indigo-300 transition">
                        <div class="flex items-center gap-2.5 text-indigo-600 font-bold text-xs mb-1.5">
                            <span class="grid size-6 place-items-center rounded-lg bg-indigo-100 text-indigo-700 text-xs font-black">4</span>
                            <span>Penawaran Promo & Upselling</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pahami promo resmi hari ini. Tawarkan bundling aksesoris (anti gores, casing, fast charging) saat konsumen memilih unit utama.
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-sky-100 bg-sky-50/70 p-3 text-xs text-sky-950 flex items-center gap-2.5">
                    <svg class="size-5 text-sky-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    <span>Lakukan follow-up leads secara berkala dan perbarui status keep barang tepat waktu.</span>
                </div>
            @endif
        </div>

        <!-- Action Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <button type="button" 
                    onclick="dismissLoginGuidance()" 
                    class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-100 transition text-center">
                {{ $modalIsSuperAdmin ? 'Saya Mengerti & Mulai Bekerja' : 'Saya Mengerti & Siap Melayani' }}
            </button>

            <a href="javascript:void(0)" 
               onclick="dismissLoginGuidance('{{ \App\Support\RouteCipher::url('guidance.index') }}')"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl {{ $modalIsSuperAdmin ? 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/25' : 'bg-sky-600 hover:bg-sky-500 shadow-sky-600/25' }} px-5 py-2.5 text-xs font-bold text-white shadow-md transition">
                <span>{{ $modalIsSuperAdmin ? 'Buka Rumus & Panduan Lengkap' : 'Buka SOP Lengkap' }}</span>
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    </div>
</div>

<script>
window.dismissLoginGuidance = function(redirectUrl) {
    const modal = document.getElementById('login-guidance-modal');
    const backdrop = document.getElementById('login-guidance-backdrop');
    const panel = document.getElementById('login-guidance-panel');

    if (backdrop && panel) {
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
    }

    // Call server asynchronously to dismiss session flag
    fetch('{{ route('guidance.dismiss') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).catch(function(e) {
        console.error('Failed to dismiss guidance session', e);
    });

    setTimeout(function() {
        if (modal) {
            modal.style.display = 'none';
        }
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    }, 250);
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('login-guidance-modal');
        if (modal && modal.style.display !== 'none') {
            dismissLoginGuidance();
        }
    }
});
</script>

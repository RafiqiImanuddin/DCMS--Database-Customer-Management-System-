<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP - DCMS Gadgetmart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-x-hidden bg-slate-950 p-4 py-8">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.28),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.16),_transparent_34%)] pointer-events-none"></div>

    <main class="relative w-full max-w-md rounded-3xl border border-white/10 bg-white p-7 shadow-[0_24px_80px_rgba(0,0,0,.35)] sm:p-9">
        
        <!-- Header -->
        <div class="mb-6 flex items-center gap-3.5">
            <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="size-14 shrink-0 rounded-2xl bg-white object-contain p-1 shadow-md border border-slate-100">
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="rounded bg-amber-50 px-2 py-0.5 text-[10px] font-extrabold text-amber-700 border border-amber-200 uppercase tracking-wider">Keamanan Data</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight mt-0.5">Verifikasi Kode OTP</h1>
            </div>
        </div>

        <!-- Notifikasi / Alerts -->
        @if (session('info'))
            <div class="mb-5 rounded-2xl bg-sky-50 border border-sky-200/80 p-3.5 text-xs text-sky-900">
                <div class="flex items-start gap-2">
                    <svg class="size-4 shrink-0 text-sky-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-3.5 text-xs text-emerald-900">
                <div class="flex items-start gap-2">
                    <svg class="size-4 shrink-0 text-emerald-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-2xl bg-rose-50 border border-rose-200/80 p-3.5 text-xs text-rose-900">
                <div class="flex items-start gap-2">
                    <svg class="size-4 shrink-0 text-rose-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('demo_otp'))
            <div class="mb-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-3.5 text-xs text-emerald-950">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 font-bold">
                        <span>🔑 Kode OTP Percobaan (Local Dev):</span>
                    </div>
                    <code class="rounded bg-emerald-700 text-white px-2 py-0.5 font-mono text-sm font-black tracking-widest">{{ session('demo_otp') }}</code>
                </div>
                <p class="text-[10px] text-emerald-800 mt-1">Gunakan kode di atas untuk langsung verifikasi di lingkungan lokal.</p>
            </div>
        @endif

        <!-- Card Instruksi -->
        <div class="mb-6 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600 space-y-1.5">
            <p class="leading-relaxed">
                Kode 6 digit telah dikirimkan ke alamat email:
            </p>
            <div class="flex items-center gap-2 font-mono font-bold text-slate-900 text-sm bg-white p-2.5 rounded-xl border border-slate-200">
                <svg class="size-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <span class="truncate">{{ $email }}</span>
            </div>
            @if (!empty($phone))
                <div class="text-[11px] text-slate-500 flex items-center gap-1 pt-1">
                    <svg class="size-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.974-.94 5.966 5.966 0 0 0 .97-2.677C3.996 15.86 3 14.03 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    <span>Salinan OTP juga dikirimkan ke WhatsApp: <strong>{{ $phone }}</strong></span>
                </div>
            @endif
        </div>

        <!-- Form Masukkan Kode OTP -->
        <form method="POST" action="{{ route('register.otp.verify') }}" class="space-y-4">
            @csrf

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider" for="otpInput">
                        Masukkan 6 Digit Kode OTP <span class="text-rose-500">*</span>
                    </label>
                    <span id="otpCountdown" class="text-xs font-mono font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
                        10:00
                    </span>
                </div>

                <input id="otpInput" 
                       name="otp" 
                       type="text" 
                       maxlength="6" 
                       pattern="[0-9]{6}" 
                       inputmode="numeric" 
                       autocomplete="one-time-code" 
                       placeholder="••••••" 
                       required 
                       autofocus 
                       class="w-full text-center tracking-[0.5em] text-2xl font-mono font-black py-3.5 rounded-2xl border-2 border-slate-200 bg-slate-50 focus:bg-white focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 transition">
                
                @error('otp')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="dcms-primary-button w-full py-3.5 mt-2 font-bold text-sm shadow-lg shadow-brand-600/25 flex items-center justify-center gap-2">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                <span>Verifikasi OTP & Aktifkan Akun</span>
            </button>
        </form>

        <!-- Actions: Resend OTP & Cancel -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col items-center gap-3 text-center">
            <form method="POST" action="{{ route('register.otp.resend') }}" class="w-full">
                @csrf
                <button type="submit" 
                        id="btnResendOtp" 
                        class="text-xs font-bold text-brand-600 hover:text-brand-700 transition cursor-pointer">
                    Belum menerima kode? <span class="underline">Kirim Ulang Kode OTP</span>
                </button>
            </form>

            <form method="POST" action="{{ route('register.cancel') }}">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 transition">
                    ← Ubah Data Pendaftaran / Kembali
                </button>
            </form>
        </div>
    </main>

    <script>
        // Countdown Timer Expire
        const expireTimestamp = {{ (int) $expiresAt }} * 1000;
        const countdownEl = document.getElementById('otpCountdown');

        function updateCountdown() {
            const now = Date.now();
            const diff = Math.max(0, Math.floor((expireTimestamp - now) / 1000));
            if (!countdownEl) return;

            const m = Math.floor(diff / 60);
            const s = diff % 60;
            countdownEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

            if (diff <= 0) {
                countdownEl.textContent = 'Kedaluwarsa';
                countdownEl.className = 'text-xs font-mono font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md';
            }
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Auto uppercase / numbers on input
        const otpInput = document.getElementById('otpInput');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>

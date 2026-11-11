<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Akun - DCMS Gadgetmart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-x-hidden bg-slate-950 p-4 py-8">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.28),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.16),_transparent_34%)] pointer-events-none"></div>

    <main class="relative w-full max-w-lg rounded-3xl border border-white/10 bg-white p-7 shadow-[0_24px_80px_rgba(0,0,0,.35)] sm:p-9">
        <div class="mb-7 flex items-center gap-4">
            <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="size-16 shrink-0 rounded-2xl bg-white object-contain p-1 shadow-md">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Daftar Akun Baru</h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">Customer Data Management System - Gadgetmart</p>
            </div>
        </div>

        <!-- Info Keamanan Verifikasi OTP -->
        <div class="mb-6 rounded-2xl bg-amber-50/70 border border-amber-200/80 p-3.5 text-xs text-amber-900 space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-amber-800">
                <svg class="size-4 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>Standar Keamanan Data Internal:</span>
            </div>
            <p class="text-[11px] leading-relaxed text-slate-600 pl-5.5">
                Setiap pendaftaran akun baru <strong>wajib diverifikasi menggunakan kode OTP 6 digit</strong> yang dikirimkan ke Email / WhatsApp Anda sebelum akun dapat diaktifkan.
            </p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-700" for="name">Nama Lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Contoh: Ahmad Rizki" required autofocus class="dcms-input text-sm">
                @error('name')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-700" for="email">Alamat Email (Untuk Pengiriman OTP) <span class="text-rose-500">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@gadgetmart.id" required class="dcms-input text-sm">
                @error('email')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-700" for="phone">Nomor WhatsApp / HP (Opsional - Cadangan OTP)</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" class="dcms-input text-sm">
                @error('phone')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-700" for="password">Kata Sandi</label>
                    <input id="password" name="password" type="password" placeholder="Minimal 6 karakter" required class="dcms-input text-sm">
                    @error('password')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-slate-700" for="password_confirmation">Konfirmasi Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi kata sandi" required class="dcms-input text-sm">
                </div>
            </div>

            <button type="submit" class="dcms-primary-button w-full py-3.5 mt-2 font-bold text-sm shadow-lg shadow-brand-600/25 flex items-center justify-center gap-2">
                <span>Lanjutkan & Kirim Kode OTP</span>
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 transition">Masuk di sini</a>
            </p>
        </div>
    </main>
</body>
</html>

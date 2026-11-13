@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('container-class', 'mx-auto max-w-5xl p-4 md:p-8')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pengaturan Akun</h1>
            <p class="text-sm text-slate-500 font-medium">Kelola informasi profil, email, dan kata sandi akun Anda.</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold {{ $user->isSuperAdmin() ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($user->isAdmin() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-brand-50 text-brand-700 border border-brand-200') }}">
                <span class="size-2 rounded-full {{ $user->isSuperAdmin() ? 'bg-amber-500' : ($user->isAdmin() ? 'bg-emerald-500' : 'bg-brand-500') }}"></span>
                Peran Akun: {{ $user->isSuperAdmin() ? 'Superadmin' : ($user->isAdmin() ? 'Admin' : 'Staff / User') }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 shadow-sm flex items-center gap-3">
            <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Profil Card & Hak Akses -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Profil Singkat -->
            <div class="dcms-card p-6 text-center space-y-4">
                <div class="mx-auto flex size-20 items-center justify-center rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 text-2xl font-black text-white shadow-lg shadow-brand-500/25">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $user->email }}</p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Terdaftar sejak</span>
                    <span class="font-bold text-slate-700">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                </div>
            </div>

            <!-- Kartu Hak Akses Menu -->
            <div class="dcms-card p-5 space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                    <svg class="size-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Hak Akses Menu Anda</span>
                </h3>
                <ul class="text-xs space-y-2 text-slate-600">
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Customer & Outreach (Dashboard, Master, Leads, Broadcast)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Workspace Sosmed (Dashboard & Report Matrix)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Panduan & SOP Sales</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Pengaturan Akun Sendiri</span>
                    </li>
                    @if ($user->isSuperAdmin())
                        <li class="flex items-center gap-2 text-amber-700 font-bold">
                            <svg class="size-4 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Akses Penuh Superadmin (Pricelist, Import, WA, Karyawan)</span>
                        </li>
                    @else
                        <li class="flex items-center gap-2 text-slate-400">
                            <svg class="size-4 text-slate-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <span class="line-through">Pricelist Gadget & Setting Master (Khusus Superadmin)</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Kolom Kanan: Form Ubah Profil & Password -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Form 1: Ubah Data Profil -->
            <div class="dcms-card p-6">
                <h3 class="text-base font-black text-slate-900 tracking-tight mb-1">Informasi Profil</h3>
                <p class="text-xs text-slate-500 mb-5">Perbarui nama tampilan dan alamat email akun Anda.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="dcms-input text-sm">
                        @error('name')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="dcms-input text-sm">
                        @error('email')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="dcms-primary-button px-5 py-2.5 text-xs font-bold">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form 2: Ganti Password -->
            <div class="dcms-card p-6">
                <h3 class="text-base font-black text-slate-900 tracking-tight mb-1">Ganti Kata Sandi</h3>
                <p class="text-xs text-slate-500 mb-5">Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.</p>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" required placeholder="Masukkan kata sandi lama Anda" class="dcms-input text-sm">
                        @error('current_password')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                            <input type="password" id="new_password" name="password" required placeholder="Minimal 6 karakter" class="dcms-input text-sm">
                            @error('password')
                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Ulangi Kata Sandi Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Konfirmasi kata sandi" class="dcms-input text-sm">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-slate-800 transition">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form 3: Preferensi Tampilan Widget Live Chat -->
            <div class="dcms-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight mb-1 flex items-center gap-2">
                            <span>Preferensi Tampilan (Floating Widget)</span>
                        </h3>
                        <p class="text-xs text-slate-500">Atur apakah tombol Live Chat WhatsApp di pojok kanan bawah ditampilkan atau disembunyikan.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.profile.toggle_live_chat_widget') }}" class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-4">
                    @csrf
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-bold text-base shrink-0">
                            💬
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Floating Live WhatsApp Chat Widget</div>
                            <div class="text-[11px] text-slate-500">Ikon melayang hijau di pojok kanan bawah untuk interaksi chat WhatsApp cepat.</div>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="enabled" value="1" {{ $user->canAccessFeature('workspace_live_chat_widget') ? 'checked' : '' }} onchange="this.form.submit()" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

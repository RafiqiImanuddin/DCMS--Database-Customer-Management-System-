<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - DCMS Gadgetmart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 p-4">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.28),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.16),_transparent_34%)]"></div>
    <main class="relative w-full max-w-md rounded-3xl border border-white/10 bg-white p-7 shadow-[0_24px_80px_rgba(0,0,0,.35)] sm:p-9">
        <div class="mb-8 flex items-center gap-4">
            <img src="{{ asset('images/gadgetmart-logo.png') }}" alt="Gadgetmart" class="size-16 shrink-0 rounded-2xl bg-white object-contain p-1 shadow-md">
            <div><h1 class="text-xl font-black text-slate-900">DCMS Gadgetmart</h1><p class="mt-1 text-sm text-slate-500">Customer Data Management System</p></div>
        </div>
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="dcms-input">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold" for="password">Password</label>
                <input id="password" name="password" type="password" required class="dcms-input">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Ingat saya
            </label>
            <button class="dcms-primary-button w-full py-3.5">Masuk ke Dashboard</button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-700 transition">Daftar Akun Baru</a>
            </p>
        </div>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login DCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-950 p-4">
    <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold">DCMS Repeat Order</h1>
            <p class="mt-1 text-sm text-slate-500">Masuk untuk mengelola data dan prediksi.</p>
        </div>
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg border border-slate-300 px-3 py-3 focus:border-blue-600 focus:outline-none">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold" for="password">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-lg border border-slate-300 px-3 py-3 focus:border-blue-600 focus:outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Ingat saya
            </label>
            <button class="w-full rounded-lg bg-blue-700 px-4 py-3 font-semibold text-white hover:bg-blue-800">Masuk</button>
        </form>
    </main>
</body>
</html>

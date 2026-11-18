<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trim($__env->yieldContent('tab_title')) ?: (trim($__env->yieldContent('title')) ? trim($__env->yieldContent('title')) . ' - DCMS - SOSMED' : 'DCMS - SOSMED') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/gadgetmart-logo.png') }}?v={{ @filemtime(public_path('images/gadgetmart-logo.png')) ?: '1.0' }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/gadgetmart-logo.png') }}?v={{ @filemtime(public_path('images/gadgetmart-logo.png')) ?: '1.0' }}">
    <link rel="shortcut icon" href="{{ asset('images/gadgetmart-logo.png') }}?v={{ @filemtime(public_path('images/gadgetmart-logo.png')) ?: '1.0' }}">
    <link rel="apple-touch-icon" href="{{ asset('images/gadgetmart-logo.png') }}?v={{ @filemtime(public_path('images/gadgetmart-logo.png')) ?: '1.0' }}">
    @yield('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            try {
                if (localStorage.getItem('dcms_sidebar_collapsed') === '1' && window.innerWidth >= 768) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div id="sidebar-overlay" onclick="if(window.toggleDcmsSidebar){window.toggleDcmsSidebar(false)}" class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm md:hidden"></div>
        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur md:px-8">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button id="sidebar-toggle" onclick="if(window.toggleDcmsSidebar){window.toggleDcmsSidebar()}" type="button" class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs cursor-pointer" aria-label="Toggle Sidebar" title="Sembunyikan / Tampilkan Sidebar (Ctrl+B)">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div><div class="text-sm font-bold text-slate-900">@yield('title')</div><div class="hidden text-xs text-slate-400 sm:block">DCMS - SOSMED</div></div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m11 9h4a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3h-4"/></svg><span class="hidden sm:inline">Keluar</span></button>
                        </form>
                    </div>
                </div>
            </header>
            <div class="@yield('container-class', 'mx-auto max-w-[1600px] p-4 md:p-8')">
                @if (auth()->check() && auth()->user()->isGuest())
                    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50/90 px-4 py-3 text-xs text-amber-900 shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-6 shrink-0 place-items-center rounded-lg bg-amber-500 font-black text-white text-[11px]">!</span>
                            <div>
                                <span class="font-bold">Mode Tamu (Guest • Read-Only):</span>
                                <span class="text-amber-800"> Anda dapat meninjau seluruh menu sistem. Fitur modifikasi (tambah/edit/hapus) dinonaktifkan dan data pribadi (No. HP Konsumen, Pricelist & Pencapaian) disensor.</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-full bg-amber-200/70 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-amber-900">
                            🔒 Mode Aman
                        </span>
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Reusable Confirmation Modal (Modern UI) -->
    <div id="dcms-confirm-modal" class="fixed inset-0 z-[999999] hidden items-center justify-center p-4" aria-modal="true" role="dialog">
        <!-- Backdrop with Blur -->
        <div id="dcms-confirm-backdrop" onclick="closeConfirmModal()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-200 opacity-0 cursor-pointer"></div>

        <!-- Modal Box -->
        <div id="dcms-confirm-panel" class="relative z-10 w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all duration-200 scale-95 opacity-0 border border-slate-100">
            <!-- Close button -->
            <button type="button" onclick="closeConfirmModal()" class="absolute right-4 top-4 rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer" aria-label="Tutup">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-start gap-4">
                <!-- Icon Badge -->
                <div id="dcms-confirm-icon-bg" class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 shadow-xs">
                    <svg id="dcms-confirm-icon-danger" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <svg id="dcms-confirm-icon-warning" class="hidden size-6 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>

                <!-- Text Content -->
                <div class="flex-1 pr-4">
                    <h3 id="dcms-confirm-title" class="text-base font-black text-slate-900 tracking-tight">Konfirmasi Tindakan</h3>
                    <p id="dcms-confirm-message" class="mt-1.5 text-xs text-slate-500 leading-relaxed">Data yang dihapus tidak dapat dipulihkan kembali.</p>

                    <!-- Optional Item Highlight -->
                    <div id="dcms-confirm-item-box" class="mt-3 hidden rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2 text-xs text-slate-700">
                        <span class="text-slate-400 text-[11px] block">Target:</span>
                        <span id="dcms-confirm-item-name" class="font-bold text-slate-900 break-all text-xs"></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeConfirmModal()" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="button" id="dcms-confirm-btn" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 hover:bg-rose-500 focus:ring-2 focus:ring-rose-500/20 transition">
                    <span id="dcms-confirm-btn-text">Ya, Hapus Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentConfirmCallback = null;

    function openConfirmModal(options = {}) {
        const modal = document.getElementById('dcms-confirm-modal');
        const backdrop = document.getElementById('dcms-confirm-backdrop');
        const panel = document.getElementById('dcms-confirm-panel');
        if (!modal || !backdrop || !panel) return;

        document.getElementById('dcms-confirm-title').textContent = options.title || 'Konfirmasi Tindakan';
        document.getElementById('dcms-confirm-message').textContent = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        
        const itemBox = document.getElementById('dcms-confirm-item-box');
        const itemName = document.getElementById('dcms-confirm-item-name');
        if (options.itemName) {
            itemName.textContent = options.itemName;
            itemBox.classList.remove('hidden');
        } else {
            itemBox.classList.add('hidden');
        }

        const confirmBtn = document.getElementById('dcms-confirm-btn');
        const confirmBtnText = document.getElementById('dcms-confirm-btn-text');
        confirmBtnText.textContent = options.confirmText || 'Ya, Hapus Sekarang';

        const iconDanger = document.getElementById('dcms-confirm-icon-danger');
        const iconWarning = document.getElementById('dcms-confirm-icon-warning');
        const iconBg = document.getElementById('dcms-confirm-icon-bg');

        if (options.type === 'warning') {
            iconBg.className = 'flex size-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 shadow-xs';
            iconDanger.classList.add('hidden');
            iconWarning.classList.remove('hidden');
            confirmBtn.className = 'inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-amber-600/25 hover:bg-amber-500 focus:ring-2 focus:ring-amber-500/20 transition';
        } else {
            iconBg.className = 'flex size-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 shadow-xs';
            iconDanger.classList.remove('hidden');
            iconWarning.classList.add('hidden');
            confirmBtn.className = 'inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 hover:bg-rose-500 focus:ring-2 focus:ring-rose-500/20 transition';
        }

        currentConfirmCallback = options.onConfirm || null;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeConfirmModal() {
        const modal = document.getElementById('dcms-confirm-modal');
        const backdrop = document.getElementById('dcms-confirm-backdrop');
        const panel = document.getElementById('dcms-confirm-panel');
        if (!modal || !backdrop || !panel) return;

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            currentConfirmCallback = null;
        }, 200);
    }

    document.getElementById('dcms-confirm-btn')?.addEventListener('click', function() {
        if (typeof currentConfirmCallback === 'function') {
            const cb = currentConfirmCallback;
            closeConfirmModal();
            cb();
        } else {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('dcms-confirm-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeConfirmModal();
            }
        }
    });

    function confirmForm(buttonOrForm, options = {}) {
        const form = buttonOrForm.tagName === 'FORM' ? buttonOrForm : buttonOrForm.closest('form');
        if (!form) return;

        try {
            openConfirmModal({
                title: options.title || 'Konfirmasi Hapus Data',
                message: options.message || 'Apakah Anda yakin ingin menghapus data ini secara permanen dari sistem?',
                itemName: options.item || options.itemName || '',
                confirmText: options.confirmText || 'Ya, Hapus Sekarang',
                type: options.type || 'danger',
                onConfirm: () => {
                    const btn = form.querySelector('button[type="button"], button[type="submit"]') || (buttonOrForm.tagName === 'BUTTON' ? buttonOrForm : null);
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-60', 'cursor-not-allowed');
                        btn.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="animate-spin size-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menghapus...</span>';
                    }
                    form.submit();
                }
            });
        } catch (err) {
            console.error('Modal error, using fallback confirm:', err);
            const promptText = (options.title ? options.title + '\n' : '') + (options.itemName ? 'Item: ' + options.itemName + '\n' : '') + (options.message || 'Apakah Anda yakin?');
            if (window.confirm(promptText)) {
                form.submit();
            }
        }
    }
    </script>
    @if (session('show_login_guidance') && auth()->check())
        @php
            session()->forget('show_login_guidance');
        @endphp
        @include('guidance._login_modal')
    @endif

    {{-- Global Anti-Autofill Protection untuk Seluruh Kolom Pencarian DCMS --}}
    <script>
    (function initDcmsSearchAntiAutofill() {
        const currentUserEmail = {{ auth()->check() ? json_encode(auth()->user()->email) : "''" }};

        function sanitizeAllSearchInputs() {
            const selectors = [
                'input[type="search"]',
                'input[name="search"]',
                'input[name="q"]',
                'input[name*="search" i]',
                'input[name*="cari" i]',
                'input[id*="search" i]',
                'input[id*="cari" i]',
                'input[placeholder*="cari" i]',
                'input[placeholder*="search" i]'
            ];

            const inputs = document.querySelectorAll(selectors.join(','));
            inputs.forEach(inp => {
                // Pastikan bukan input di dalam modal edit user atau profil akun
                if (inp.closest('#userFormModal, #profileFormModal, form[action*="profile"], form[action*="users"]')) {
                    return;
                }

                // Pasang atribut keras anti-autofill
                if (inp.getAttribute('autocomplete') !== 'new-password') {
                    inp.setAttribute('autocomplete', 'new-password');
                }
                inp.setAttribute('autocorrect', 'off');
                inp.setAttribute('autocapitalize', 'off');
                inp.setAttribute('spellcheck', 'false');
                inp.setAttribute('data-lpignore', 'true');
                inp.setAttribute('data-form-type', 'other');

                const val = (inp.value || '').trim();
                // Deteksi jika terisi email akun atau password manager autofill
                const isAutofilledEmail = currentUserEmail && val.toLowerCase() === currentUserEmail.toLowerCase();
                const isGenericEmail = val.includes('@') && (val.includes('.com') || val.includes('.id') || val.includes('.net') || val.includes('admin'));

                if (isAutofilledEmail || isGenericEmail) {
                    inp.value = '';
                    inp.dispatchEvent(new Event('input', { bubbles: true }));
                    inp.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', sanitizeAllSearchInputs);
        } else {
            sanitizeAllSearchInputs();
        }

        // Loop interval untuk menangkal autofill Chrome yang terinjeksi setelah delay
        [50, 150, 300, 600, 1000, 1800, 3000].forEach(delay => {
            setTimeout(sanitizeAllSearchInputs, delay);
        });
    })();
    </script>

    {{-- Global DCMS Route Masker: Pastikan URL di address bar browser SELALU bertopeng xxxx-xxxx-xxxx --}}
    <script>
    (function enforceMaskedRouteInBrowser() {
        const maskedToken = @json(\App\Support\RouteCipher::encode(\App\Support\RouteCipher::resolveTarget(request()), array_filter(request()->query())));
        const currentActiveTab = {{ (int) (\App\Support\RouteCipher::resolveWorkspaceTab(request()) ?? 0) }};
        const workspaceTokens = @json(\App\Support\WorkspaceUrlCipher::allTokens());

        function sanitizeBrowserUrl() {
            try {
                let targetToken = maskedToken;
                if (currentActiveTab > 0 && workspaceTokens && workspaceTokens[currentActiveTab]) {
                    targetToken = workspaceTokens[currentActiveTab];
                }

                if (targetToken && /^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/.test(targetToken)) {
                    const expectedPath = '/r/' + targetToken;
                    if (window.location.pathname !== expectedPath || window.location.search.length > 0) {
                        window.history.replaceState({}, '', expectedPath);
                    }
                }
            } catch(e) {}
        }

        sanitizeBrowserUrl();
        document.addEventListener('DOMContentLoaded', sanitizeBrowserUrl);
        window.addEventListener('load', sanitizeBrowserUrl);
    })();
    </script>

</body>
</html>

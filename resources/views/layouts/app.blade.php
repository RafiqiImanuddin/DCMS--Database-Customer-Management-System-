<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - DCMS Gadgetmart</title>
    @yield('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $encryptedCurrentUrl = \App\Support\RouteCipher::resolveCurrentEncryptedUrl(request());
    @endphp
    @if ($encryptedCurrentUrl)
    <script>
        (function() {
            try {
                if (!window.location.pathname.startsWith('/r/')) {
                    window.history.replaceState({}, '', '{{ $encryptedCurrentUrl }}');
                }
            } catch (e) {}
        })();
    </script>
    @endif
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div id="sidebar-overlay" onclick="if(window.toggleDcmsSidebar){window.toggleDcmsSidebar(false)}" class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm md:hidden"></div>
        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur md:px-8">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button id="sidebar-toggle" onclick="if(window.toggleDcmsSidebar){window.toggleDcmsSidebar()}" type="button" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 md:hidden" aria-label="Buka menu">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div><div class="text-sm font-bold text-slate-900">@yield('title')</div><div class="hidden text-xs text-slate-400 sm:block">DCMS Gadgetmart</div></div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m11 9h4a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3h-4"/></svg><span class="hidden sm:inline">Keluar</span></button>
                    </form>
                </div>
            </header>
            <div class="mx-auto max-w-[1600px] p-4 md:p-8">
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
    <div id="dcms-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" aria-modal="true" role="dialog">
        <!-- Backdrop with Blur -->
        <div id="dcms-confirm-backdrop" onclick="closeConfirmModal()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-200 opacity-0"></div>

        <!-- Modal Box -->
        <div id="dcms-confirm-panel" class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all duration-200 scale-95 opacity-0 border border-slate-100">
            <!-- Close button -->
            <button type="button" onclick="closeConfirmModal()" class="absolute right-4 top-4 rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" aria-label="Tutup">
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

        openConfirmModal({
            title: options.title || 'Konfirmasi Hapus Data',
            message: options.message || 'Apakah Anda yakin ingin menghapus data ini secara permanen dari sistem?',
            itemName: options.item || options.itemName || '',
            confirmText: options.confirmText || 'Ya, Hapus Sekarang',
            type: options.type || 'danger',
            onConfirm: () => form.submit()
        });
    }

    // Modern Custom Dropdown UX Enhancer for all Selects
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function getOptionBadge(text) {
        const lower = (text || '').toLowerCase();
        if (lower.includes('whatsapp valid') || lower.includes('online') || lower.includes('terkirim') || lower.includes('sukses')) {
            return '<span class="inline-block size-2 rounded-full bg-emerald-500 mr-2 shrink-0"></span>';
        }
        if (lower.includes('tidak valid') || lower.includes('offline') || lower.includes('gagal') || lower.includes('failed')) {
            return '<span class="inline-block size-2 rounded-full bg-rose-500 mr-2 shrink-0"></span>';
        }
        if (lower.includes('tanpa nomor') || lower.includes('missing') || lower.includes('pending')) {
            return '<span class="inline-block size-2 rounded-full bg-amber-400 mr-2 shrink-0"></span>';
        }
        if (lower.startsWith('semua') || lower.includes('all')) {
            return '<span class="inline-block size-2 rounded-full bg-blue-500 mr-2 shrink-0"></span>';
        }
        return '';
    }

    function initDcmsCustomSelects(root = document) {
        const selects = root.querySelectorAll('select.dcms-input:not([multiple]):not([data-no-custom-select]), select.dcms-select, select.modern-filter-select');
        selects.forEach(select => {
            if (select.dataset.customSelectInit) return;
            select.dataset.customSelectInit = 'true';

            // Hide native select visually while keeping it accessible in DOM & forms
            select.style.position = 'absolute';
            select.style.opacity = '0';
            select.style.pointerEvents = 'none';
            select.style.width = '1px';
            select.style.height = '1px';
            select.tabIndex = -1;

            const wrapper = document.createElement('div');
            wrapper.className = 'dcms-custom-select-wrapper';
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dcms-custom-select-btn';
            
            const selectedOpt = select.options[select.selectedIndex] || select.options[0];
            const btnContent = document.createElement('span');
            btnContent.className = 'inline-flex items-center truncate';
            btnContent.innerHTML = selectedOpt ? getOptionBadge(selectedOpt.text) + escapeHtml(selectedOpt.text) : 'Pilih...';

            const chevron = document.createElement('span');
            chevron.innerHTML = '<svg class="dcms-custom-select-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4"/></svg>';

            btn.appendChild(btnContent);
            btn.appendChild(chevron.firstElementChild);
            wrapper.appendChild(btn);

            const dropdown = document.createElement('div');
            dropdown.className = 'dcms-custom-select-dropdown hidden';

            function renderOptions() {
                dropdown.innerHTML = '';
                Array.from(select.options).forEach((opt, idx) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'dcms-custom-select-item' + (opt.selected ? ' is-selected' : '');
                    
                    const leftSpan = document.createElement('span');
                    leftSpan.className = 'inline-flex items-center truncate';
                    leftSpan.innerHTML = getOptionBadge(opt.text) + escapeHtml(opt.text);

                    item.appendChild(leftSpan);

                    if (opt.selected) {
                        const check = document.createElement('span');
                        check.innerHTML = '<svg class="size-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
                        item.appendChild(check.firstElementChild);
                    }

                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        select.selectedIndex = idx;
                        select.value = opt.value;
                        btnContent.innerHTML = getOptionBadge(opt.text) + escapeHtml(opt.text);
                        closeAllCustomSelects();
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    dropdown.appendChild(item);
                });
            }

            renderOptions();
            wrapper.appendChild(dropdown);

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !dropdown.classList.contains('hidden');
                closeAllCustomSelects();
                if (!isOpen) {
                    renderOptions();
                    dropdown.classList.remove('hidden');
                    btn.classList.add('is-active');
                }
            });

            select.addEventListener('change', () => {
                const cur = select.options[select.selectedIndex];
                if (cur) {
                    btnContent.innerHTML = getOptionBadge(cur.text) + escapeHtml(cur.text);
                }
                renderOptions();
            });
        });
    }

    function closeAllCustomSelects() {
        document.querySelectorAll('.dcms-custom-select-dropdown').forEach(d => d.classList.add('hidden'));
        document.querySelectorAll('.dcms-custom-select-btn').forEach(b => b.classList.remove('is-active'));
    }

    document.addEventListener('click', () => closeAllCustomSelects());
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAllCustomSelects();
    });

    document.addEventListener('DOMContentLoaded', () => initDcmsCustomSelects());
    </script>
</body>
</html>

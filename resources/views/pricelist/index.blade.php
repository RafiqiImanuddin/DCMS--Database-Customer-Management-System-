@extends('layouts.app')

@section('title', 'Pricelist & Skema Gadget')

@section('content')
<div class="space-y-6">
    <!-- Header Page Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 md:p-8 text-white shadow-xl border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-bold mb-3">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Superadmin Only • View-Only Reference</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Pricelist & Skema Gadget</h1>
                <p class="mt-1.5 text-xs md:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Katalog harga modal, SRP Promo, Cashback (PWP & Trade-in), Margin, Skema MPL, hingga Modal IMEI terpadu untuk referensi internal.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl px-4 py-2.5 text-right">
                    <span class="text-[10px] text-slate-400 block font-medium">Terakhir Diperbarui</span>
                    <span id="labelLastUpdated" class="text-xs font-bold text-white flex items-center justify-end gap-1.5">
                        <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ $updatedAt }}
                    </span>
                </div>

                <!-- Tombol Sync Live Dropbox -->
                <button type="button" id="btnSyncDropbox" onclick="handleSyncDropbox()" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2.5 text-xs font-bold shadow-lg shadow-emerald-600/30 transition cursor-pointer">
                    <svg id="syncDropboxIcon" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span id="syncDropboxText">Sync Live Dropbox</span>
                </button>

                <button type="button" onclick="document.getElementById('modalUploadPricelist').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/10 text-white px-3.5 py-2.5 text-xs font-bold transition cursor-pointer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                    <span>Upload Manual</span>
                </button>
            </div>
        </div>
        <!-- Decorative Glow -->
        <div class="absolute -right-20 -top-20 size-80 rounded-full bg-brand-500/10 blur-3xl pointer-events-none"></div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div onclick="switchPricelistTab('harga_fix')" class="cursor-pointer bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:border-brand-500 hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>HARGA FIX</span>
                <span class="size-2 rounded-full bg-brand-500"></span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($sheets['harga_fix']['total'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Tipe HP, Margin & MPL</div>
        </div>

        <div onclick="switchPricelistTab('update_harga_sm')" class="cursor-pointer bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:border-blue-500 hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>Update Harga SM</span>
                <span class="size-2 rounded-full bg-blue-500"></span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($sheets['update_harga_sm']['total'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Modal, Cashback & Subsidi</div>
        </div>

        <div onclick="switchPricelistTab('xiaomi')" class="cursor-pointer bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:border-amber-500 hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>HP Xiaomi Store</span>
                <span class="size-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($sheets['xiaomi']['total'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">DTP, SIP & Margin Tax</div>
        </div>

        <div onclick="switchPricelistTab('modal_imei')" class="cursor-pointer bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:border-emerald-500 hover:shadow-md transition">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>Modal By IMEI</span>
                <span class="size-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($sheets['modal_imei']['total'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Serial IMEI & Modal Unit</div>
        </div>
    </div>

    <!-- Main Table Container Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <!-- Tab Navigation & Filters Toolbar -->
        <div class="p-4 md:p-6 border-b border-slate-200/80 bg-slate-50/50">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- 4 Tabs Switcher -->
                <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-200/60 w-fit">
                    <button type="button" onclick="switchPricelistTab('harga_fix')" id="tab-btn-harga_fix" class="pricelist-tab-btn px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer bg-white text-brand-600 shadow-xs">
                        1. HARGA FIX ({{ number_format($sheets['harga_fix']['total'] ?? 0, 0, ',', '.') }})
                    </button>
                    <button type="button" onclick="switchPricelistTab('update_harga_sm')" id="tab-btn-update_harga_sm" class="pricelist-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer">
                        2. Update Harga - SM ({{ number_format($sheets['update_harga_sm']['total'] ?? 0, 0, ',', '.') }})
                    </button>
                    <button type="button" onclick="switchPricelistTab('xiaomi')" id="tab-btn-xiaomi" class="pricelist-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer">
                        3. HP Xiaomi Store ({{ number_format($sheets['xiaomi']['total'] ?? 0, 0, ',', '.') }})
                    </button>
                    <button type="button" onclick="switchPricelistTab('modal_imei')" id="tab-btn-modal_imei" class="pricelist-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer">
                        4. Modal By IMEI ({{ number_format($sheets['modal_imei']['total'] ?? 0, 0, ',', '.') }})
                    </button>
                </div>

                <!-- Search & Filters -->
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 sm:w-72">
                        <svg class="size-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" id="pricelistSearchInput" oninput="filterPricelistTable()" placeholder="Cari tipe, nama HP, IMEI..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-2xs">
                    </div>
                    <select id="pricelistBrandFilter" onchange="filterPricelistTable()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-2xs cursor-pointer">
                        <option value="ALL">Semua Brand</option>
                        <option value="APPLE">Apple / iPhone / iPad</option>
                        <option value="SAMSUNG">Samsung</option>
                        <option value="OPPO">Oppo</option>
                        <option value="VIVO">Vivo</option>
                        <option value="XIAOMI">Xiaomi / Redmi / Poco</option>
                        <option value="REALME">Realme</option>
                        <option value="INFINIX">Infinix</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- TAB 1: HARGA FIX -->
        <div id="tab-content-harga_fix" class="pricelist-tab-content">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900 text-white font-bold tracking-wider uppercase text-[11px] border-b border-slate-800">
                            <th class="py-3.5 px-4 sticky left-0 bg-slate-900 z-10">Tipe / Item Name</th>
                            <th class="py-3.5 px-4">Brand</th>
                            <th class="py-3.5 px-4 text-right">Modal Sesudah CB</th>
                            <th class="py-3.5 px-4 text-right">SRP Promo</th>
                            <th class="py-3.5 px-4 text-right">Margin</th>
                            <th class="py-3.5 px-4 text-right">Modal Akhir</th>
                            <th class="py-3.5 px-4 text-right">Margin M. Akhir</th>
                            <th class="py-3.5 px-4 text-right">Harga MPL</th>
                            <th class="py-3.5 px-4 text-right">Admin MPL</th>
                            <th class="py-3.5 px-4 text-right">Margin MPL</th>
                            <th class="py-3.5 px-4">Ket Program</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-harga_fix" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: UPDATE HARGA - SM -->
        <div id="tab-content-update_harga_sm" class="pricelist-tab-content hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900 text-white font-bold tracking-wider uppercase text-[11px] border-b border-slate-800">
                            <th class="py-3.5 px-4 sticky left-0 bg-slate-900 z-10">Item Name</th>
                            <th class="py-3.5 px-4">Brand</th>
                            <th class="py-3.5 px-4 text-right">Modal Sblm CB</th>
                            <th class="py-3.5 px-4 text-right">SRP</th>
                            <th class="py-3.5 px-4 text-right">CB Reguler</th>
                            <th class="py-3.5 px-4 text-right">CB PWP</th>
                            <th class="py-3.5 px-4 text-right">CB Partner</th>
                            <th class="py-3.5 px-4 text-right">CB Trade-In</th>
                            <th class="py-3.5 px-4 text-right">Subsidi Leasing</th>
                            <th class="py-3.5 px-4">Ket Program</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-update_harga_sm" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: HP XIAOMI STORE -->
        <div id="tab-content-xiaomi" class="pricelist-tab-content hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900 text-white font-bold tracking-wider uppercase text-[11px] border-b border-slate-800">
                            <th class="py-3.5 px-4">Product ID</th>
                            <th class="py-3.5 px-4 sticky left-0 bg-slate-900 z-10">Name</th>
                            <th class="py-3.5 px-4">Variant</th>
                            <th class="py-3.5 px-4 text-right">SRP</th>
                            <th class="py-3.5 px-4 text-right">DTP W/O Tax</th>
                            <th class="py-3.5 px-4 text-right">SIP With Tax</th>
                            <th class="py-3.5 px-4 text-right">Margin Rate</th>
                            <th class="py-3.5 px-4 text-right">Margin</th>
                            <th class="py-3.5 px-4 text-right">Margin + Tax</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-xiaomi" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: MODAL BY IMEI -->
        <div id="tab-content-modal_imei" class="pricelist-tab-content hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900 text-white font-bold tracking-wider uppercase text-[11px] border-b border-slate-800">
                            <th class="py-3.5 px-4 font-mono">IMEI / Serial</th>
                            <th class="py-3.5 px-4">Tipe Barang</th>
                            <th class="py-3.5 px-4 text-right">Modal Unit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-modal_imei" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination & Row Counter -->
        <div class="p-4 border-t border-slate-200/80 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Menampilkan <span id="pricelistShowingCount" class="font-bold text-slate-900">0</span> dari <span id="pricelistTotalCount" class="font-bold text-slate-900">0</span> data
            </div>
            <div class="flex items-center gap-2" id="pricelistPagination">
                <!-- Pagination buttons inserted by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Excel Refresh -->
<div id="modalUploadPricelist" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-black text-slate-900 tracking-tight">Update File Pricelist Excel</h3>
            <button type="button" onclick="document.getElementById('modalUploadPricelist').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>
        <form id="formUploadPricelist" onsubmit="handleUploadPricelist(event)" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File Excel (.xlsx)</label>
                <input type="file" id="pricelistFileInput" name="file" accept=".xlsx,.xls" required class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition cursor-pointer">
                <span class="text-[11px] text-slate-400 block mt-1.5">File akan otomatis diekstrak menjadi 4 sheet utama.</span>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalUploadPricelist').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btnSubmitUploadPricelist" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-md shadow-brand-600/20 transition">Unggah & Ekstrak</button>
            </div>
        </form>
    </div>
</div>

<script>
window.PRICELIST_DATA = @json($sheets);

let currentTab = 'harga_fix';
let currentPage = 1;
const PAGE_SIZE = 50;

function formatRupiah(val) {
    if (!val || isNaN(val) || Number(val) === 0) return '-';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

function switchPricelistTab(tabName) {
    currentTab = tabName;
    currentPage = 1;

    document.querySelectorAll('.pricelist-tab-btn').forEach(btn => {
        btn.className = 'pricelist-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer';
    });
    const activeBtn = document.getElementById('tab-btn-' + tabName);
    if (activeBtn) {
        activeBtn.className = 'pricelist-tab-btn px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer bg-white text-brand-600 shadow-xs';
    }

    document.querySelectorAll('.pricelist-tab-content').forEach(c => c.classList.add('hidden'));
    const activeContent = document.getElementById('tab-content-' + tabName);
    if (activeContent) activeContent.classList.remove('hidden');

    filterPricelistTable();
}

function filterPricelistTable() {
    const rawData = (window.PRICELIST_DATA && window.PRICELIST_DATA[currentTab] && window.PRICELIST_DATA[currentTab].data) ? window.PRICELIST_DATA[currentTab].data : [];
    const keyword = (document.getElementById('pricelistSearchInput').value || '').trim().toLowerCase();
    const brandFilter = document.getElementById('pricelistBrandFilter').value;

    const filtered = rawData.filter(item => {
        // Brand filter if present
        if (brandFilter !== 'ALL' && item.brand && item.brand !== brandFilter) {
            return false;
        }

        // Search text filter
        if (keyword) {
            const str = JSON.stringify(item).toLowerCase();
            if (!str.includes(keyword)) return false;
        }
        return true;
    });

    renderPricelistRows(filtered);
}

function renderPricelistRows(items) {
    const total = items.length;
    const totalPages = Math.ceil(total / PAGE_SIZE) || 1;
    currentPage = Math.min(currentPage, totalPages);
    const startIdx = (currentPage - 1) * PAGE_SIZE;
    const paginated = items.slice(startIdx, startIdx + PAGE_SIZE);

    document.getElementById('pricelistShowingCount').textContent = `${total > 0 ? startIdx + 1 : 0} - ${Math.min(startIdx + PAGE_SIZE, total)}`;
    document.getElementById('pricelistTotalCount').textContent = total.toLocaleString('id-ID');

    const tbody = document.getElementById('tbody-' + currentTab);
    if (!tbody) return;

    if (paginated.length === 0) {
        tbody.innerHTML = `<tr><td colspan="15" class="py-12 text-center text-slate-400 font-semibold text-xs">Tidak ada data ditemukan.</td></tr>`;
        renderPagination(totalPages);
        return;
    }

    if (currentTab === 'harga_fix') {
        tbody.innerHTML = paginated.map(row => `
            <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-bold text-slate-900 sticky left-0 bg-white hover:bg-slate-50">${row.type}</td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">${row.brand}</span></td>
                <td class="py-3 px-4 text-right font-medium text-slate-600">${formatRupiah(row.modal_sesudah_cb)}</td>
                <td class="py-3 px-4 text-right font-bold text-brand-600">${formatRupiah(row.srp_promo)}</td>
                <td class="py-3 px-4 text-right font-bold ${row.margin < 0 ? 'text-rose-600' : 'text-emerald-600'}">${formatRupiah(row.margin)}</td>
                <td class="py-3 px-4 text-right font-medium text-slate-600">${formatRupiah(row.modal_akhir)}</td>
                <td class="py-3 px-4 text-right font-bold text-emerald-700">${formatRupiah(row.margin_modal_akhir)}</td>
                <td class="py-3 px-4 text-right font-bold text-slate-900">${formatRupiah(row.harga_mpl)}</td>
                <td class="py-3 px-4 text-right text-slate-500">${formatRupiah(row.admin_mpl)}</td>
                <td class="py-3 px-4 text-right font-bold ${row.margin_mpl < 0 ? 'text-rose-600' : 'text-emerald-600'}">${formatRupiah(row.margin_mpl)}</td>
                <td class="py-3 px-4 text-slate-500">${row.ket_program || '-'}</td>
            </tr>
        `).join('');
    } else if (currentTab === 'update_harga_sm') {
        tbody.innerHTML = paginated.map(row => `
            <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-bold text-slate-900 sticky left-0 bg-white hover:bg-slate-50">${row.item_name}</td>
                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">${row.brand}</span></td>
                <td class="py-3 px-4 text-right font-medium text-slate-600">${formatRupiah(row.modal_sebelum_cb)}</td>
                <td class="py-3 px-4 text-right font-bold text-blue-600">${formatRupiah(row.srp)}</td>
                <td class="py-3 px-4 text-right text-emerald-600 font-semibold">${formatRupiah(row.cb_regular)}</td>
                <td class="py-3 px-4 text-right text-slate-600">${formatRupiah(row.cb_pwp)}</td>
                <td class="py-3 px-4 text-right text-slate-600">${formatRupiah(row.cb_partner)}</td>
                <td class="py-3 px-4 text-right text-slate-600">${formatRupiah(row.cb_trade_in)}</td>
                <td class="py-3 px-4 text-right text-amber-600 font-bold">${formatRupiah(row.subsidi_leasing)}</td>
                <td class="py-3 px-4 text-slate-500">${row.ket_program || '-'}</td>
            </tr>
        `).join('');
    } else if (currentTab === 'xiaomi') {
        tbody.innerHTML = paginated.map(row => `
            <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-mono text-slate-500 font-bold">${row.product_id || '-'}</td>
                <td class="py-3 px-4 font-bold text-slate-900 sticky left-0 bg-white hover:bg-slate-50">${row.name}</td>
                <td class="py-3 px-4 font-semibold text-slate-600">${row.variant || '-'}</td>
                <td class="py-3 px-4 text-right font-bold text-amber-600">${formatRupiah(row.srp)}</td>
                <td class="py-3 px-4 text-right text-slate-600">${formatRupiah(row.dtp_wo_tax)}</td>
                <td class="py-3 px-4 text-right font-medium text-slate-900">${formatRupiah(row.sip_with_tax)}</td>
                <td class="py-3 px-4 text-right font-bold text-slate-700">${(row.margin_rate * 100).toFixed(1)}%</td>
                <td class="py-3 px-4 text-right font-bold text-emerald-600">${formatRupiah(row.margin)}</td>
                <td class="py-3 px-4 text-right font-bold text-emerald-700">${formatRupiah(row.margin_plus_tax)}</td>
            </tr>
        `).join('');
    } else if (currentTab === 'modal_imei') {
        tbody.innerHTML = paginated.map(row => `
            <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-mono font-bold text-emerald-700">${row.imei}</td>
                <td class="py-3 px-4 font-semibold text-slate-900">${row.tipe}</td>
                <td class="py-3 px-4 text-right font-black text-slate-900">${formatRupiah(row.modal)}</td>
            </tr>
        `).join('');
    }

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const container = document.getElementById('pricelistPagination');
    if (!container) return;
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <button type="button" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-slate-200 cursor-pointer"'} class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-700 transition">Sebelumnya</button>
        <span class="text-xs font-bold text-slate-700 px-2">${currentPage} / ${totalPages}</span>
        <button type="button" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-slate-200 cursor-pointer"'} class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-700 transition">Berikutnya</button>
    `;
}

function goToPage(page) {
    currentPage = page;
    filterPricelistTable();
}

async function handleSyncDropbox() {
    const btn = document.getElementById('btnSyncDropbox');
    const icon = document.getElementById('syncDropboxIcon');
    const text = document.getElementById('syncDropboxText');

    btn.disabled = true;
    text.textContent = 'Mengunduh dari Dropbox...';
    icon.classList.add('animate-spin');

    try {
        const res = await fetch('{{ route("pricelist.sync_dropbox") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            if (typeof window.showDcmsToast === 'function') {
                window.showDcmsToast({
                    title: 'Pricelist Dropbox Sinkron!',
                    message: 'Data harga terbaru berhasil diunduh dan diekstrak langsung dari Dropbox.',
                    type: 'success'
                });
            } else {
                alert('Pricelist berhasil disinkronkan langsung dari Dropbox!');
            }

            if (data.sheets) {
                window.PRICELIST_DATA = data.sheets;
                filterPricelistTable();
            }
            if (data.updated_at) {
                document.getElementById('labelLastUpdated').innerHTML = `<span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span> ${data.updated_at}`;
            }
        } else {
            alert(data.message || 'Gagal sinkronisasi dari Dropbox.');
        }
    } catch (err) {
        alert('Gagal menghubungi server Dropbox: ' + err.message);
    } finally {
        btn.disabled = false;
        text.textContent = 'Sync Live Dropbox';
        icon.classList.remove('animate-spin');
    }
}

async function handleUploadPricelist(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitUploadPricelist');
    const input = document.getElementById('pricelistFileInput');
    if (!input.files || input.files.length === 0) return;

    btn.disabled = true;
    btn.textContent = 'Mengekstrak Excel...';

    const formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('_token', '{{ csrf_token() }}');

    try {
        const res = await fetch('{{ route("pricelist.refresh") }}', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alert('Pricelist berhasil diperbarui!');
            window.location.reload();
        } else {
            alert(data.message || 'Gagal memperbarui file.');
        }
    } catch (err) {
        alert('Terjadi kesalahan saat memproses file Excel: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Unggah & Ekstrak';
    }
}

// Initial Render
document.addEventListener('DOMContentLoaded', () => {
    switchPricelistTab('harga_fix');
});
</script>
@endsection

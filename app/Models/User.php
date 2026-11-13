<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /**
     * Cek apakah akun memiliki hak akses Superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return ($this->role ?? 'admin') === 'superadmin';
    }

    /**
     * Cek apakah akun memiliki hak akses Admin (atau Superadmin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin'], true);
    }

    /**
     * Cek apakah akun merupakan user default / staff biasa.
     */
    public function isDefaultUser(): bool
    {
        return ! $this->isAdmin() && ! $this->isGuest();
    }

    /**
     * Cek apakah akun merupakan akun Tamu (Guest).
     */
    public function isGuest(): bool
    {
        return ($this->role ?? '') === 'guest';
    }

    /**
     * Definisi seluruh modul/fitur yang dapat diaktifkan/dinonaktifkan melalui checklist izin akun.
     *
     * @return array<string, array{name: string, description: string, category: string, default_roles: array<string>}>
     */
    public static function availableFeatures(): array
    {
        return [
            // Kategori 1: Customer & Outreach CRM
            'dashboard' => [
                'name' => 'Dashboard Utama',
                'description' => 'Akses ringkasan statistik, performa penjualan, dan grafik KPI.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin', 'user'],
            ],
            'predictions' => [
                'name' => 'Master Customer & Prediksi',
                'description' => 'Melihat data master customer dan prediksi repeat order AI.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin', 'user'],
            ],
            'predictions_assign' => [
                'name' => 'Assign Leads Customer',
                'description' => 'Menugaskan customer/leads ke admin sales atau CS.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin'],
            ],
            'predictions_download' => [
                'name' => 'Download Data Customer',
                'description' => 'Mengunduh / ekspor file Excel atau CSV data customer.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin'],
            ],
            'leads' => [
                'name' => 'Leads Customer',
                'description' => 'Melihat daftar calon pembeli dan memantau status interaksi leads.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin', 'user'],
            ],
            'broadcasts' => [
                'name' => 'Laporan Broadcast WhatsApp',
                'description' => 'Melihat rekap blast WhatsApp, status terkirim, gagal, dan antrean.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin', 'user'],
            ],
            'broadcasts_create' => [
                'name' => 'Kirim Broadcast & Kelola Template',
                'description' => 'Membuat pesan blast baru dan menambah/mengubah template broadcast.',
                'category' => 'Customer & Outreach',
                'default_roles' => ['admin'],
            ],
            'pricelist' => [
                'name' => 'Pricelist Gadget & Skema',
                'description' => 'Akses katalog harga gadgetmart, skema kredit, dan sinkronisasi excel.',
                'category' => 'Customer & Outreach',
                'default_roles' => [],
            ],

            // Kategori 2: Workspace Sosmed
            'workspace_dashboard' => [
                'name' => 'Workspace: Dashboard Sosmed',
                'description' => 'Akses Tab 1: Ringkasan KPI dan grafik performa tim sosmed.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin', 'user'],
            ],
            'workspace_matrix' => [
                'name' => 'Workspace: Report Matrix',
                'description' => 'Akses Tab 2: Laporan Daily Matrix, Omzet, GP, dan Cross-Selling.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin', 'user'],
            ],
            'workspace_cek_ar' => [
                'name' => 'Workspace: Cek Status AR',
                'description' => 'Akses Tab 3: Cek status Account Receivable dan outstanding nota.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_format_keep' => [
                'name' => 'Workspace: Format Keep',
                'description' => 'Akses Tab 4: Pencatatan form keep barang / booking pelanggan.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_format_keep_delete' => [
                'name' => 'Workspace: Hapus Data Format Keep',
                'description' => 'Izin menghapus baris transaksi booking / keep customer di Tab 4 Format Keep.',
                'category' => 'Workspace Sosmed',
                'default_roles' => [],
            ],
            'workspace_ola_cabang' => [
                'name' => 'Workspace: Dashboard OLA Cabang',
                'description' => 'Akses Tab 10: Dashboard performa OLA / SLA kecepatan respon claim keep cabang.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_tracking_ar' => [
                'name' => 'Workspace: Tracking AR',
                'description' => 'Akses Tab 5: Riwayat pelacakan dan konfirmasi pelunasan piutang AR.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_klaim_ar' => [
                'name' => 'Workspace: Klaim AR',
                'description' => 'Akses Tab 6: Pengajuan dan verifikasi klaim nota/AR cabang.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_inbox_fu' => [
                'name' => 'Workspace: Inbox Follow-Up',
                'description' => 'Akses Tab 9: Inbox interaksi pesan WhatsApp dan tindak lanjut FU.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_live_chat_widget' => [
                'name' => 'Floating Live Chat Widget WhatsApp',
                'description' => 'Menampilkan ikon melayang Live WhatsApp Inbox di pojok kanan bawah layar.',
                'category' => 'Workspace Sosmed',
                'default_roles' => ['admin'],
            ],
            'workspace_archives' => [
                'name' => 'Workspace: Arsip Pencapaian',
                'description' => 'Akses Tab 7: Riwayat arsip pencapaian bulanan tim admin sales.',
                'category' => 'Workspace Sosmed',
                'default_roles' => [],
            ],
            'workspace_admin_settings' => [
                'name' => 'Workspace: Pengaturan Admin',
                'description' => 'Akses Tab 8: Upload foto profil sales dan target omzet sosmed.',
                'category' => 'Workspace Sosmed',
                'default_roles' => [],
            ],

            // Kategori 3: Dashboard Manager
            'monitoring_sales' => [
                'name' => 'Monitoring Admin Sales',
                'description' => 'Evaluasi scorecard harian, sinkronisasi KPI, dan peninjauan sales.',
                'category' => 'Dashboard Manager',
                'default_roles' => ['admin'],
            ],
            'sosmed_shifts' => [
                'name' => 'Jadwal Shift Admin Sosmed',
                'description' => 'Kelola jadwal piket & shift kerja admin sosmed (Pagi, Siang, Lembur, Off).',
                'category' => 'Dashboard Manager',
                'default_roles' => ['admin', 'user'],
            ],
            'monitoring_sales_export' => [
                'name' => 'Export Rekap Monitoring',
                'description' => 'Unduh laporan rekap penilaian KPI sales ke file Excel.',
                'category' => 'Dashboard Manager',
                'default_roles' => ['admin'],
            ],
            'quotations' => [
                'name' => 'Surat Penawaran Harga',
                'description' => 'Membuat, mengedit, mencetak, dan mengelola arsip surat penawaran harga.',
                'category' => 'Dashboard Manager',
                'default_roles' => ['admin'],
            ],

            // Kategori 4: Panduan & SOP
            'guidance' => [
                'name' => 'Panduan & SOP Operasional',
                'description' => 'Dokumen SOP sales, pedoman kerja, dan rumus perhitungan potensi.',
                'category' => 'Panduan & SOP',
                'default_roles' => ['admin', 'user'],
            ],

            // Kategori 5: Pengaturan Sistem
            'imports' => [
                'name' => 'Pusat Unggah Data (SAP / Turnover)',
                'description' => 'Import file Excel transaksi cross-selling SAP dan omzet cabang.',
                'category' => 'Pengaturan Sistem',
                'default_roles' => [],
            ],
            'watzap_settings' => [
                'name' => 'Setup WhatsApp Gateway (WatZap)',
                'description' => 'Konfigurasi nomor gateway, API key WatZap, dan pendaftaran webhook.',
                'category' => 'Pengaturan Sistem',
                'default_roles' => ['admin'],
            ],
            'employee_settings' => [
                'name' => 'Data Karyawan & Bobot KPI',
                'description' => 'Kelola master data admin sales dan bobot kriteria penilaian kinerja.',
                'category' => 'Pengaturan Sistem',
                'default_roles' => [],
            ],
            'monthly_targets' => [
                'name' => 'Target Omzet Bulanan',
                'description' => 'Mengatur target omzet bulanan cabang dan evaluasi target tim.',
                'category' => 'Pengaturan Sistem',
                'default_roles' => [],
            ],
        ];
    }

    /**
     * Dapatkan daftar kategori fitur untuk pengelompokan di tampilan checklist.
     *
     * @return array<string, array<string, array{name: string, description: string, category: string, default_roles: array<string>}>>
     */
    public static function groupedFeatures(): array
    {
        $grouped = [];
        foreach (static::availableFeatures() as $key => $feature) {
            $category = $feature['category'];
            if (! isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$key] = $feature;
        }

        return $grouped;
    }

    /**
     * Dapatkan daftar default permissions untuk sebuah role.
     *
     * @return array<string>
     */
    public static function defaultPermissionsForRole(string $role): array
    {
        if ($role === 'superadmin') {
            return array_keys(static::availableFeatures());
        }

        if ($role === 'guest') {
            $blockedForGuest = [
                'predictions_assign',
                'predictions_download',
                'broadcasts_create',
                'monitoring_sales_export',
                'workspace_format_keep_delete',
            ];

            return array_values(array_diff(array_keys(static::availableFeatures()), $blockedForGuest));
        }

        $permissions = [];
        foreach (static::availableFeatures() as $key => $meta) {
            if (in_array($role, $meta['default_roles'], true)) {
                $permissions[] = $key;
            }
        }

        return $permissions;
    }

    /**
     * Periksa apakah user berwenang mengakses fitur tertentu.
     * Superadmin selalu memiliki akses penuh (true).
     * Tamu (Guest) dapat mengakses semua menu lihat, diblokir dari aksi mutasi/download.
     * Akun selain superadmin diperiksa melalui checklist permissions (atau fallback ke default role).
     */
    public function canAccessFeature(string $feature): bool
    {
        // Fitur khusus UI: Live Chat Widget dapat dinonaktifkan per akun (termasuk akun Superadmin)
        if ($feature === 'workspace_live_chat_widget') {
            if ($this->permissions !== null && is_array($this->permissions)) {
                return in_array('workspace_live_chat_widget', $this->permissions, true);
            }

            return $this->isSuperAdmin() || $this->isAdmin();
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isGuest()) {
            $blockedForGuest = [
                'predictions_assign',
                'predictions_download',
                'broadcasts_create',
                'monitoring_sales_export',
                'workspace_format_keep_delete',
            ];

            return ! in_array($feature, $blockedForGuest, true);
        }

        if ($this->permissions !== null && is_array($this->permissions)) {
            if (in_array($feature, $this->permissions, true)) {
                return true;
            }

            // Fallback: Dashboard OLA Cabang otomatis terbuka jika Format Keep diizinkan
            if ($feature === 'workspace_ola_cabang') {
                return in_array('workspace_format_keep', $this->permissions, true);
            }

            return false;
        }

        // Fallback izin bawaan jika permissions belum diatur secara eksplisit
        $features = static::availableFeatures();
        if (isset($features[$feature])) {
            $userRole = $this->role ?? 'user';
            return in_array($userRole, $features[$feature]['default_roles'], true);
        }

        return false;
    }

    /**
     * Dapatkan daftar key fitur aktif untuk akun ini.
     *
     * @return array<string>
     */
    public function getEffectivePermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return array_keys(static::availableFeatures());
        }

        if ($this->permissions !== null && is_array($this->permissions)) {
            return $this->permissions;
        }

        return static::defaultPermissionsForRole($this->role ?? 'user');
    }

    /**
     * Rute pertama yang dapat diakses oleh user ini saat redirect setelah login.
     */
    public function getFirstAccessibleRoute(): string
    {
        if ($this->canAccessFeature('dashboard')) {
            return route('dashboard');
        }

        if ($this->canAccessFeature('predictions')) {
            return route('predictions.index');
        }

        if ($this->canAccessFeature('workspace_dashboard') || $this->canAccessFeature('workspace_matrix')) {
            return route('workspace-sosmed.index');
        }

        if ($this->canAccessFeature('leads')) {
            return route('leads.index');
        }

        if ($this->canAccessFeature('broadcasts')) {
            return route('broadcasts.index');
        }

        if ($this->canAccessFeature('guidance')) {
            return route('guidance.index');
        }

        return route('profile.edit');
    }
}

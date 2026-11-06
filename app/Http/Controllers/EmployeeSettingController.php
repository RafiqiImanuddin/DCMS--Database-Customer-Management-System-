<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\KpiCriterion;
use App\Models\RepeatOrderPrediction;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeSettingController extends Controller
{
    /**
     * Display employee list and KPI weights configuration.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $cabang = $request->string('cabang')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $activeTab = $request->string('tab', 'employees')->toString();

        $employeeQuery = Employee::query()->withCount('assessments');

        if ($search !== '') {
            $employeeQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($cabang !== '') {
            $employeeQuery->where('cabang', $cabang);
        }

        if ($status !== '') {
            $employeeQuery->where('status', $status);
        }

        $employees = $employeeQuery->orderBy('cabang')->orderBy('name')->paginate(25)->withQueryString();

        $kpiCriteria = KpiCriterion::query()->orderBy('sort_order')->orderBy('id')->get();
        $totalWeight = (float) $kpiCriteria->where('is_active', true)->sum('weight');

        // Master weights for Sosmed Report Matrix
        $matrixWeightsSetting = Setting::where('key', 'workspace_matrix_weights')->value('value');
        $matrixWeights = ! empty($matrixWeightsSetting) ? json_decode($matrixWeightsSetting, true) : [
            'omzet' => 35.0,
            'gp' => 35.0,
            'cross_selling' => 30.0,
        ];
        $totalMatrixWeight = (float) (($matrixWeights['omzet'] ?? 0) + ($matrixWeights['gp'] ?? 0) + ($matrixWeights['cross_selling'] ?? 0));

        // Fetch list of branches for employee dropdown from employees + repeat_order_predictions
        $branches = RepeatOrderPrediction::query()
            ->whereNotNull('cabang')
            ->where('cabang', '!=', '')
            ->distinct()
            ->orderBy('cabang')
            ->pluck('cabang')
            ->filter()
            ->values()
            ->all();

        if (empty($branches)) {
            $branches = ['Head Office', 'AMTC', 'BDM3', 'BJB2', 'BJBC', 'BJMA', 'BJMC', 'BJMD', 'BJMP', 'TJGC'];
        } else {
            $branches = array_values(array_unique(array_merge(['Head Office'], $branches)));
        }

        return view('settings.karyawan', compact(
            'employees',
            'kpiCriteria',
            'totalWeight',
            'matrixWeights',
            'totalMatrixWeight',
            'branches',
            'search',
            'cabang',
            'status',
            'activeTab'
        ));
    }

    /**
     * Store a new employee record.
     */
    public function storeEmployee(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang menambah data karyawan.');

        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:50', 'unique:employees,nik'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:100'],
            'target' => ['nullable', 'numeric', 'min:0'],
            'cabang' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'joined_date' => ['nullable', 'date'],
            'avatar_color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($validated['target'])) {
            $validated['target'] = 867261886.00;
        }

        if (empty($validated['avatar_color'])) {
            $palette = ['#2563eb', '#059669', '#7c3aed', '#d97706', '#db2777', '#0891b2', '#4f46e5', '#ea580c'];
            $validated['avatar_color'] = $palette[array_rand($palette)];
        }

        Employee::create($validated);

        return redirect()->route('settings.karyawan.index', ['tab' => 'employees'])
            ->with('success', "Data karyawan {$validated['name']} ({$validated['nik']}) berhasil ditambahkan.");
    }

    /**
     * Update an existing employee record.
     */
    public function updateEmployee(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang mengubah data karyawan.');

        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:50', 'unique:employees,nik,' . $employee->id],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:100'],
            'target' => ['nullable', 'numeric', 'min:0'],
            'cabang' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'joined_date' => ['nullable', 'date'],
            'avatar_color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! isset($validated['target']) || $validated['target'] === null || $validated['target'] === '') {
            $validated['target'] = $employee->target ?? 867261886.00;
        }

        $employee->update($validated);

        return redirect()->route('settings.karyawan.index', ['tab' => 'employees'])
            ->with('success', "Data karyawan {$employee->name} berhasil diperbarui.");
    }

    /**
     * Delete an employee record.
     */
    public function destroyEmployee(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang menghapus data karyawan.');

        $name = $employee->name;
        $employee->delete();

        return redirect()->route('settings.karyawan.index', ['tab' => 'employees'])
            ->with('success', "Data karyawan {$name} telah dihapus.");
    }

    /**
     * Update weights and benchmarks for KPI criteria and Report Matrix.
     */
    public function updateKpiWeights(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang mengubah bobot KPI.');

        $matrixWeights = $request->input('matrix_weights', []);
        $weights = $request->input('weights', []);
        $benchmarks = $request->input('benchmarks', []);
        $descriptions = $request->input('descriptions', []);
        $actives = $request->input('actives', []);

        // 1. Process Sosmed Report Matrix Weights (Omzet, GP, Cross Selling)
        if (! empty($matrixWeights) && is_array($matrixWeights)) {
            $wOmzet = (float) ($matrixWeights['omzet'] ?? 0);
            $wGp = (float) ($matrixWeights['gp'] ?? 0);
            $wCross = (float) ($matrixWeights['cross_selling'] ?? 0);
            $totalMatrix = $wOmzet + $wGp + $wCross;

            if (abs($totalMatrix - 100.0) > 0.05) {
                return redirect()->route('settings.karyawan.index', ['tab' => 'weights'])
                    ->with('error', "Total bobot Rapor Matrix Sosmed (Omzet + GP + Cross-Selling) harus tepat 100%. Saat ini totalnya: {$totalMatrix}%. Silakan sesuaikan kembali.");
            }

            Setting::updateOrCreate(
                ['key' => 'workspace_matrix_weights'],
                ['value' => json_encode([
                    'omzet' => $wOmzet,
                    'gp' => $wGp,
                    'cross_selling' => $wCross,
                ])]
            );
        }

        // 2. Process Qualitative KPI Criteria (Manager Evaluation)
        if (! empty($weights) && is_array($weights)) {
            $totalActiveWeight = 0.0;
            foreach ($weights as $id => $weight) {
                $isActive = ! empty($actives[$id]);
                if ($isActive) {
                    $totalActiveWeight += (float) $weight;
                }
            }

            // Allow tiny float tolerance (e.g. 99.99 to 100.01)
            if (abs($totalActiveWeight - 100.0) > 0.05) {
                return redirect()->route('settings.karyawan.index', ['tab' => 'weights'])
                    ->with('error', "Total bobot kriteria kualitatif aktif harus tepat 100%. Saat ini totalnya: {$totalActiveWeight}%. Silakan sesuaikan kembali.");
            }

            foreach ($weights as $id => $weight) {
                $criterion = KpiCriterion::find($id);
                if ($criterion) {
                    $criterion->update([
                        'weight' => max(0, min(100, (float) $weight)),
                        'target_benchmark' => $benchmarks[$id] ?? $criterion->target_benchmark,
                        'description' => $descriptions[$id] ?? $criterion->description,
                        'is_active' => ! empty($actives[$id]),
                    ]);
                }
            }
        }

        return redirect()->route('settings.karyawan.index', ['tab' => 'weights'])
            ->with('success', 'Bobot KPI (Rapor Matrix Sosmed & Evaluasi Kinerja) berhasil disimpan dan langsung tersinkronkan!');
    }

    /**
     * Store a new custom KPI criterion.
     */
    public function storeKpiCriterion(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang menambah kriteria KPI.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:kpi_criteria,code'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_benchmark' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $maxSort = (int) KpiCriterion::max('sort_order');
        $validated['sort_order'] = $maxSort + 1;
        $validated['is_active'] = true;

        KpiCriterion::create($validated);

        return redirect()->route('settings.karyawan.index', ['tab' => 'weights'])
            ->with('success', "Kriteria penilaian baru '{$validated['name']}' berhasil ditambahkan. Pastikan total bobot tetap 100%.");
    }

    /**
     * Delete a custom KPI criterion.
     */
    public function destroyKpiCriterion(Request $request, KpiCriterion $criterion): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses terbatas: Hanya Superadmin yang berwenang menghapus kriteria KPI.');

        $name = $criterion->name;
        $criterion->delete();

        return redirect()->route('settings.karyawan.index', ['tab' => 'weights'])
            ->with('success', "Kriteria '{$name}' telah dihapus. Sesuaikan kembali bobot kriteria lainnya agar total 100%.");
    }
}


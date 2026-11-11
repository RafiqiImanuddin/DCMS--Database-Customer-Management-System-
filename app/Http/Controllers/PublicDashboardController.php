<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\SosmedShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicDashboardController extends Controller
{
    /**
     * Tampilkan Live Dashboard Publik (Views Only):
     * Menampilkan Report Matrix Sosmed & Jadwal Shift Karyawan.
     * Dapat diakses secara umum oleh publik / tim tanpa login.
     */
    public function index(Request $request): View
    {
        // 1. Data Metadata Workspace & Bobot Matrix
        $matrixWeightsSetting = Setting::where('key', 'workspace_matrix_weights')->value('value');
        $matrixWeights = ! empty($matrixWeightsSetting) ? json_decode($matrixWeightsSetting, true) : [
            'omzet' => 35.0,
            'gp' => 35.0,
            'cross_selling' => 30.0,
        ];

        $monthlyTargetsSetting = Setting::where('key', 'workspace_monthly_targets')->value('value');
        $monthlyTargets = ! empty($monthlyTargetsSetting) ? json_decode($monthlyTargetsSetting, true) : [];
        if (! is_array($monthlyTargets)) {
            $monthlyTargets = [];
        }

        // Karyawan aktif: Karyawan Tetap wajib paling atas, disusul Internship
        $employeeQuery = Employee::query()->where('status', 'active');
        if (Schema::hasColumn('employees', 'level')) {
            $employeeQuery->orderByRaw("CASE WHEN level = 'Internship' THEN 2 ELSE 1 END");
        }
        $employees = $employeeQuery->orderBy('name')->get();

        $initialWorkspaceSettings = [
            'matrix_weights' => $matrixWeights,
            'monthly_targets' => $monthlyTargets,
            'employees' => $employees->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'code' => $emp->nik,
                    'nik' => $emp->nik,
                    'name' => strtoupper($emp->name),
                    'target' => (float) ($emp->target ?: 867261886.00),
                    'active' => $emp->status === 'active',
                    'status' => $emp->status,
                    'cabang' => $emp->cabang,
                    'role' => $emp->role,
                    'level' => $emp->level ?? 'Karyawan',
                    'phone' => $emp->phone,
                    'avatar_color' => $emp->avatar_color,
                ];
            })->values()->all(),
        ];

        // 2. Data Jadwal Shift Karyawan
        $today = Carbon::today('Asia/Makassar');

        // Navigasi Minggu (Senin - Minggu)
        $weekParam = $request->string('week')->trim()->toString();
        if ($weekParam !== '') {
            try {
                $startOfWeek = Carbon::parse($weekParam, 'Asia/Makassar')->startOfWeek(Carbon::MONDAY);
            } catch (\Exception $e) {
                $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
            }
        } else {
            $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        }
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->locale('id')->isoFormat('dddd'),
                'short_date' => $date->locale('id')->isoFormat('D MMM'),
                'is_today' => $date->isSameDay($today),
            ];
        }

        // Shift Hari Ini
        $todayShifts = SosmedShift::whereDate('shift_date', $today->format('Y-m-d'))->get();
        $todayStats = [
            'pagi' => $todayShifts->where('shift_type', 'pagi'),
            'siang' => $todayShifts->where('shift_type', 'siang'),
            'lembur' => $todayShifts->where('shift_type', 'lembur'),
            'off' => $todayShifts->where('shift_type', 'off'),
            'total_active' => $todayShifts->whereIn('shift_type', ['pagi', 'siang', 'lembur'])->count(),
        ];

        // Roster shifts mingguan
        $rosterShiftsQuery = SosmedShift::whereBetween('shift_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);
        $searchAdmin = $request->string('search')->trim()->toString();
        if ($searchAdmin !== '') {
            $rosterShiftsQuery->where('admin_name', 'like', "%{$searchAdmin}%");
        }
        $rosterShifts = $rosterShiftsQuery->get();

        $activeRosterEmployees = $employees;
        if ($searchAdmin !== '') {
            $activeRosterEmployees = $employees->filter(function ($emp) use ($searchAdmin) {
                return stripos($emp->name, $searchAdmin) !== false 
                    || stripos($emp->nik, $searchAdmin) !== false 
                    || stripos($emp->level ?? '', $searchAdmin) !== false;
            })->values();
        }

        $rosterMatrix = [];
        foreach ($rosterShifts as $shift) {
            $rosterMatrix[$shift->admin_name][$shift->shift_date->format('Y-m-d')] = $shift;
        }

        $shiftDefinitions = SosmedShift::SHIFTS;
        $activeTab = $request->string('tab', 'both')->toString(); // 'both', 'matrix', 'shifts'

        return view('public-dashboard', compact(
            'today',
            'startOfWeek',
            'endOfWeek',
            'weekDays',
            'todayStats',
            'employees',
            'activeRosterEmployees',
            'rosterMatrix',
            'shiftDefinitions',
            'searchAdmin',
            'activeTab',
            'initialWorkspaceSettings'
        ));
    }
}

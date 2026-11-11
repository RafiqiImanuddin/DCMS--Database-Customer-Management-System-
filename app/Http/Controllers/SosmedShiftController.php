<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SosmedShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SosmedShiftController extends Controller
{
    /**
     * Tampilkan halaman kelola jadwal shift admin sosmed.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->canAccessFeature('sosmed_shifts'), 403, 'Akses terbatas: Anda tidak memiliki izin untuk mengelola Jadwal Shift Admin Sosmed.');

        // Format waktu & tanggal
        $today = Carbon::today('Asia/Makassar');
        $viewMode = $request->string('view')->trim()->toString() ?: 'roster'; // 'roster' (matriks mingguan) atau 'list' (tabel)

        // Penentuan tanggal awal minggu (Senin - Minggu)
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

        // Daftar 7 hari dalam minggu terpilih
        $weekDays = [];
        $cursor = $startOfWeek->copy();
        while ($cursor->lte($endOfWeek)) {
            $weekDays[] = [
                'date' => $cursor->format('Y-m-d'),
                'day_name' => $cursor->locale('id')->isoFormat('dddd'),
                'short_date' => $cursor->format('d M'),
                'is_today' => $cursor->isSameDay($today),
                'carbon' => $cursor->copy(),
            ];
            $cursor->addDay();
        }

        // Filter pencarian
        $searchAdmin = $request->string('search')->trim()->toString();
        $filterShift = $request->string('shift_type')->trim()->toString();
        $filterMonth = $request->string('month')->trim()->toString() ?: $today->format('Y-m');

        // Ringkasan jadwal Hari Ini (Today)
        $todayShifts = SosmedShift::whereDate('shift_date', $today->format('Y-m-d'))->get();
        $todayStats = [
            'pagi' => $todayShifts->where('shift_type', 'pagi'),
            'siang' => $todayShifts->where('shift_type', 'siang'),
            'lembur' => $todayShifts->where('shift_type', 'lembur'),
            'off' => $todayShifts->where('shift_type', 'off'),
            'total_active' => $todayShifts->whereIn('shift_type', ['pagi', 'siang', 'lembur'])->count(),
        ];

        // Ambil data jadwal untuk tampilan Roster (Minggu berjalan)
        $rosterShiftsQuery = SosmedShift::whereBetween('shift_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);
        if ($searchAdmin !== '') {
            $rosterShiftsQuery->where('admin_name', 'like', "%{$searchAdmin}%");
        }
        $rosterShifts = $rosterShiftsQuery->get();

        // Kumpulkan daftar karyawan HANYA dari bagian Karyawan & Bobot KPI (Model Employee)
        // Karyawan wajib berada paling atas, kemudian disusul Internship, lalu urut alfabetis
        $employeesQuery = Employee::query()->where('status', 'active');
        if (Schema::hasColumn('employees', 'level')) {
            $employeesQuery->orderByRaw("CASE WHEN level = 'Internship' THEN 2 ELSE 1 END");
        }
        $employees = $employeesQuery
            ->orderBy('name')
            ->get();

        // Daftar nama admin/karyawan untuk form dropdown & filter
        $allAdminNames = $employees->pluck('name')->values();

        // Karyawan yang tampil di baris roster minggu ini (Karyawan tetap di atas, Internship di bawah)
        $activeRosterEmployees = $employees;
        if ($searchAdmin !== '') {
            $activeRosterEmployees = $employees->filter(function ($emp) use ($searchAdmin) {
                return stripos($emp->name, $searchAdmin) !== false 
                    || stripos($emp->nik, $searchAdmin) !== false
                    || stripos($emp->level ?? '', $searchAdmin) !== false;
            })->values();
        }
        $activeRosterAdminNames = $activeRosterEmployees->pluck('name')->values();

        // Peta jadwal per admin per tanggal: [admin_name][Y-m-d] => SosmedShift
        $rosterMatrix = [];
        foreach ($rosterShifts as $shift) {
            $rosterMatrix[$shift->admin_name][$shift->shift_date->format('Y-m-d')] = $shift;
        }

        // Data untuk tampilan List View (tabel lengkap dengan paginasi)
        $listQuery = SosmedShift::query()->with(['employee', 'creator']);
        if ($searchAdmin !== '') {
            $listQuery->where('admin_name', 'like', "%{$searchAdmin}%");
        }
        if ($filterShift !== '' && array_key_exists($filterShift, SosmedShift::SHIFTS)) {
            $listQuery->where('shift_type', $filterShift);
        }
        if ($filterMonth !== '') {
            $listQuery->where('shift_date', 'like', "{$filterMonth}-%");
        }
        $listShifts = $listQuery->orderBy('shift_date', 'desc')
            ->orderBy('shift_type')
            ->orderBy('admin_name')
            ->paginate(20)
            ->withQueryString();

        $shiftDefinitions = SosmedShift::SHIFTS;

        return view('shifts.index', compact(
            'today',
            'viewMode',
            'startOfWeek',
            'endOfWeek',
            'weekDays',
            'todayStats',
            'employees',
            'activeRosterEmployees',
            'activeRosterAdminNames',
            'allAdminNames',
            'rosterMatrix',
            'listShifts',
            'searchAdmin',
            'filterShift',
            'filterMonth',
            'shiftDefinitions'
        ));
    }

    /**
     * Simpan jadwal shift baru atau perbarui jadwal jika sudah ada di tanggal yang sama.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->canAccessFeature('sosmed_shifts'), 403, 'Akses terbatas.');

        $validated = $request->validate([
            'admin_name' => ['required', 'string', 'max:100'],
            'shift_date' => ['required', 'date'],
            'shift_type' => ['required', 'in:pagi,siang,lembur,off'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'admin_name.required' => 'Nama admin sosmed wajib dipilih atau diisi.',
            'shift_date.required' => 'Tanggal jadwal shift wajib ditentukan.',
            'shift_type.required' => 'Pilihan shift wajib ditentukan (Pagi, Siang, Lembur, atau Off).',
            'shift_type.in' => 'Jenis shift tidak valid.',
        ]);

        $shiftMeta = SosmedShift::SHIFTS[$validated['shift_type']];
        $adminName = trim($validated['admin_name']);
        $matchedEmployee = Employee::where('name', $adminName)->first();
        if ($matchedEmployee) {
            $adminName = $matchedEmployee->name;
        }
        $matchedUser = User::where('name', $adminName)->orWhere('email', $matchedEmployee?->email)->first();

        $shift = SosmedShift::updateOrCreate(
            [
                'admin_name' => $adminName,
                'shift_date' => $validated['shift_date'],
            ],
            [
                'user_id' => $matchedUser?->id,
                'employee_id' => $matchedEmployee?->id,
                'shift_type' => $validated['shift_type'],
                'start_time' => $shiftMeta['start'],
                'end_time' => $shiftMeta['end'],
                'notes' => $validated['notes'] ? trim($validated['notes']) : null,
                'created_by' => $user->id,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal shift {$adminName} berhasil disimpan.",
                'shift' => $shift,
                'meta' => $shift->shift_meta,
            ]);
        }

        return back()->with('success', "Jadwal shift untuk {$adminName} pada " . Carbon::parse($validated['shift_date'])->translatedFormat('d M Y') . " ({$shiftMeta['name']}) berhasil disimpan.");
    }

    /**
     * Input atau duplikasi jadwal shift massal (Multiple Dates / Weekly Template).
     */
    public function batchStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->canAccessFeature('sosmed_shifts'), 403, 'Akses terbatas.');

        $validated = $request->validate([
            'admin_name' => ['required', 'string', 'max:100'],
            'shift_type' => ['required', 'in:pagi,siang,lembur,off'],
            'dates' => ['required', 'array', 'min:1'],
            'dates.*' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $adminName = trim($validated['admin_name']);
        $shiftMeta = SosmedShift::SHIFTS[$validated['shift_type']];
        $matchedEmployee = Employee::where('name', $adminName)->first();
        if ($matchedEmployee) {
            $adminName = $matchedEmployee->name;
        }
        $matchedUser = User::where('name', $adminName)->orWhere('email', $matchedEmployee?->email)->first();

        $count = 0;
        foreach ($validated['dates'] as $dateStr) {
            SosmedShift::updateOrCreate(
                [
                    'admin_name' => $adminName,
                    'shift_date' => $dateStr,
                ],
                [
                    'user_id' => $matchedUser?->id,
                    'employee_id' => $matchedEmployee?->id,
                    'shift_type' => $validated['shift_type'],
                    'start_time' => $shiftMeta['start'],
                    'end_time' => $shiftMeta['end'],
                    'notes' => $validated['notes'] ? trim($validated['notes']) : null,
                    'created_by' => $user->id,
                ]
            );
            $count++;
        }

        return back()->with('success', "Berhasil menerapkan shift {$shiftMeta['name']} untuk {$adminName} pada {$count} hari.");
    }

    /**
     * Perbarui jadwal shift yang sudah ada.
     */
    public function update(Request $request, SosmedShift $shift): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->canAccessFeature('sosmed_shifts'), 403, 'Akses terbatas.');

        $validated = $request->validate([
            'shift_type' => ['required', 'in:pagi,siang,lembur,off'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $shiftMeta = SosmedShift::SHIFTS[$validated['shift_type']];
        $shift->update([
            'shift_type' => $validated['shift_type'],
            'start_time' => $shiftMeta['start'],
            'end_time' => $shiftMeta['end'],
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal shift berhasil diperbarui.',
                'shift' => $shift,
                'meta' => $shift->shift_meta,
            ]);
        }

        return back()->with('success', 'Jadwal shift berhasil diperbarui.');
    }

    /**
     * Hapus jadwal shift.
     */
    public function destroy(Request $request, SosmedShift $shift): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->canAccessFeature('sosmed_shifts'), 403, 'Akses terbatas.');

        $adminName = $shift->admin_name;
        $date = $shift->shift_date->format('d M Y');
        $shift->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal shift {$adminName} tanggal {$date} telah dihapus.",
            ]);
        }

        return back()->with('success', "Jadwal shift {$adminName} tanggal {$date} telah dihapus.");
    }
}

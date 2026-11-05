<?php

namespace App\Http\Controllers;

use App\Models\KpiCriterion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuidanceController extends Controller
{
    /**
     * Display the Guidance & SOP page.
     * Superadmin receives full formulas (repeat order prediction algorithms & weighted KPI calculation).
     * Staff admin receives general operational SOPs only.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $defaultTab = $isSuperAdmin ? 'formula_potensi' : 'sop_chat';
        $activeTab = $request->string('tab', $defaultTab)->toString();

        // Enforce strict authorization: non-superadmin cannot access formula tabs
        if (! $isSuperAdmin && in_array($activeTab, ['formula_potensi', 'formula_penilaian'], true)) {
            $activeTab = 'sop_chat';
        }

        $kpiCriteria = KpiCriterion::query()->active()->orderBy('sort_order')->get();

        return view('guidance.index', compact('isSuperAdmin', 'activeTab', 'kpiCriteria', 'user'));
    }

    /**
     * Dismiss login guidance modal for current session.
     */
    public function dismissLoginPrompt(Request $request): JsonResponse
    {
        $request->session()->forget('show_login_guidance');

        return response()->json(['success' => true]);
    }
}

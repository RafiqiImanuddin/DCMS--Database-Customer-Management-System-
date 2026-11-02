<?php

namespace App\Http\Controllers;

use App\Models\BroadcastReport;
use App\Models\RepeatOrderPrediction;
use App\Services\BroadcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = BroadcastReport::query()->with(['prediction', 'user']);
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('broadcasts.index', [
            'reports' => $query->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request, RepeatOrderPrediction $prediction, BroadcastService $service): RedirectResponse
    {
        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $report = $service->send($prediction, $validated['message'], $request->user()->id);

        return redirect()->route('broadcasts.index')->with(
            'success',
            'Pesan ditambahkan ke laporan broadcast dengan status '.$report->status.'.',
        );
    }
}

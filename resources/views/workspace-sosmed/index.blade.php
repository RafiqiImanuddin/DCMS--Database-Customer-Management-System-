@extends('layouts.app')

@section('title', 'Workspace Sosmed')

@section('container-class', 'w-full px-4 py-4 md:px-8 md:py-6')

@section('head')
    <!-- Fonts: Inter for text, Plus Jakarta Sans 700-800 for titles and numbers -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Local SheetJS, JSZip, html2canvas, and Chart.js libraries -->
    <script src="{{ asset('workspace-sosmed/lib/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/jszip.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/exceljs.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/html2canvas.min.js') }}"></script>
    <script src="{{ asset('workspace-sosmed/lib/chart.umd.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('workspace-sosmed/style.css') }}?v={{ @filemtime(public_path('workspace-sosmed/style.css')) ?: '7.3' }}">
@endsection

@section('content')
<div id="dropZone" class="w-full">
    @include('workspace-sosmed.partials.subbar')
    @include('workspace-sosmed.partials.kpi-summary')

    @php
        $user = auth()->user();
    @endphp

    @if ($user?->canAccessFeature('workspace_dashboard'))
        @include('workspace-sosmed.partials.tab1-dashboard')
    @endif

    @if ($user?->canAccessFeature('workspace_matrix'))
        @include('workspace-sosmed.partials.tab2-report-matrix')
    @endif

    @if ($user?->canAccessFeature('workspace_cek_ar'))
        @include('workspace-sosmed.partials.tab3-cek-status-ar')
    @endif

    @if ($user?->canAccessFeature('workspace_format_keep'))
        @include('workspace-sosmed.partials.tab4-format-keep')
    @endif

    @if ($user?->canAccessFeature('workspace_tracking_ar'))
        @include('workspace-sosmed.partials.tab5-tracking-ar')
    @endif

    @if ($user?->canAccessFeature('workspace_klaim_ar'))
        @include('workspace-sosmed.partials.tab6-klaim-ar')
    @endif

    @if ($user?->canAccessFeature('workspace_archives'))
        @include('workspace-sosmed.partials.tab7-arsip')
    @endif

    @if ($user?->canAccessFeature('workspace_admin_settings'))
        @include('workspace-sosmed.partials.tab8-settings')
    @endif

    @if ($user?->canAccessFeature('workspace_inbox_fu'))
        @include('workspace-sosmed.partials.tab9-inbox-fu')
    @endif

    @include('workspace-sosmed.partials.modals')
    @include('workspace-sosmed.partials.scripts')
</div>
@endsection

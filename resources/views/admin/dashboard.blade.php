@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
@php
    $pendingPct = max(0, min(100, (int)($stats['pending_demandes_pct'] ?? 0)));
    $activePretsPct = max(0, min(100, (int)($stats['active_prets_pct'] ?? 0)));
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-500">Total demandes</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total_demandes'] ?? 0 }}</p>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-amber-700">Demandes en attente</p>
        <p class="mt-2 text-3xl font-extrabold text-amber-800">{{ $stats['pending_demandes'] ?? 0 }}</p>
        <p class="text-xs text-amber-700 mt-1">{{ $pendingPct }}%</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-500">Total prets</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total_prets'] ?? 0 }}</p>
    </div>

    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-emerald-700">Prets actifs</p>
        <p class="mt-2 text-3xl font-extrabold text-emerald-800">{{ $stats['active_prets'] ?? 0 }}</p>
        <p class="text-xs text-emerald-700 mt-1">{{ $activePretsPct }}%</p>
    </div>

    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-rose-700">Inscriptions en attente</p>
        <p class="mt-2 text-3xl font-extrabold text-rose-800">{{ $stats['pending_registrations'] ?? 0 }}</p>
        <a href="{{ route('admin.registrations.index') }}" class="inline-block mt-2 text-xs font-semibold text-rose-700 hover:text-rose-800">Valider maintenant</a>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Demandes par type</h2>
        <div class="h-72"><canvas id="chartDemandesType"></canvas></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Demandes par statut</h2>
        <div class="h-72"><canvas id="chartDemandesStatus"></canvas></div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Prets par statut</h2>
        <div class="h-72"><canvas id="chartPretsStatus"></canvas></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Evolution (6 derniers mois)</h2>
        <div class="h-72"><canvas id="chartEvolution"></canvas></div>
    </div>
</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Actions rapides</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <a href="{{ route('admin.registrations.index') }}" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100">Valider les inscriptions</a>
        <a href="{{ route('admin.demandes.index', ['status' => 'En attente']) }}" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 hover:bg-amber-100">Demandes en attente</a>
        <a href="{{ route('admin.prets.index', ['status' => 'active']) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">Prets actifs</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script type="application/json" id="charts-data">@json($charts ?? [], JSON_UNESCAPED_UNICODE)</script>

<script>
(() => {
    const charts = JSON.parse(document.getElementById('charts-data').textContent || '{}');

    const doughnut = (id, data) => {
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        'rgba(59,130,246,0.85)',
                        'rgba(168,85,247,0.85)',
                        'rgba(249,115,22,0.85)',
                        'rgba(34,197,94,0.85)',
                        'rgba(14,165,233,0.85)',
                        'rgba(244,114,182,0.85)',
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    };

    const bar = (id, data, pctData) => {
        new Chart(document.getElementById(id), {
            type: 'bar',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        'rgba(234,179,8,0.85)',
                        'rgba(34,197,94,0.85)',
                        'rgba(239,68,68,0.85)'
                    ],
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const label = ctx.label;
                                const pct = pctData?.[label] ?? 0;
                                return `${ctx.parsed.y} (${pct}%)`;
                            }
                        }
                    }
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    };

    doughnut('chartDemandesType', charts.demandesByType || {});
    bar('chartDemandesStatus', charts.demandesByStatus || {}, charts.demandesByStatusPct || {});
    bar('chartPretsStatus', charts.pretsByStatus || {}, charts.pretsByStatusPct || {});

    new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: {
            labels: charts.monthsLabels || [],
            datasets: [
                {
                    label: 'Demandes',
                    data: charts.demandesPerMonth || [],
                    borderColor: 'rgb(220,38,38)',
                    backgroundColor: 'rgba(220,38,38,0.15)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Prets',
                    data: charts.pretsPerMonth || [],
                    borderColor: 'rgb(34,197,94)',
                    backgroundColor: 'rgba(34,197,94,0.15)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
})();
</script>
@endsection

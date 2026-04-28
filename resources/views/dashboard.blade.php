<x-layout title="Dashboard">

@push('styles')
<style>
    .kpi-card { border-left: 4px solid; border-radius: .5rem; }
    .kpi-card.green  { border-color: #196844; }
    .kpi-card.teal   { border-color: #20c997; }
    .kpi-card.purple { border-color: #6f42c1; }
    .kpi-card.orange { border-color: #fd7e14; }
    .kpi-number { font-size: 2rem; font-weight: 700; line-height: 1; }
    .chart-card { background:#fff; border-radius:.5rem; box-shadow:0 1px 4px rgba(0,0,0,.08); }
    .chart-card .chart-title { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6c757d; }
</style>
@endpush

{{-- ── Cabecera + filtros ── --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="mb-0 fw-bold" style="color:#196844">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </h2>
        @if($periodoActual)
            <span class="text-muted small">Período: <strong>{{ $periodoActual->nombre }}</strong></span>
        @endif
    </div>

    <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap gap-2 align-items-center">
        <select name="id_periodo" class="form-select form-select-sm" style="max-width:160px" onchange="this.form.submit()">
            @foreach($periodos as $p)
                <option value="{{ $p->id_periodo }}" {{ $idPeriodo == $p->id_periodo ? 'selected' : '' }}>
                    {{ $p->nombre }}
                </option>
            @endforeach
        </select>
        <select name="id_sede" class="form-select form-select-sm" style="max-width:200px" onchange="this.form.submit()">
            <option value="">Todas las sedes</option>
            @foreach($sedes as $se)
                <option value="{{ $se->id_sede }}" {{ $idSede == $se->id_sede ? 'selected' : '' }}>
                    [{{ $se->codigo }}] {{ $se->nombre }}
                </option>
            @endforeach
        </select>
    </form>
</div>

{{-- ── KPI Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card green p-3 h-100">
            <div class="text-muted small mb-1"><i class="bi bi-clipboard2-heart-fill me-1" style="color:#196844"></i>Servicios</div>
            <div class="kpi-number" style="color:#196844">{{ number_format($totalServicios) }}</div>
            <div class="text-muted" style="font-size:.75rem">en el período</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card teal p-3 h-100">
            <div class="text-muted small mb-1"><i class="bi bi-people-fill me-1" style="color:#20c997"></i>Personas impactadas</div>
            <div class="kpi-number" style="color:#20c997">{{ number_format($totalPersonas) }}</div>
            <div class="text-muted" style="font-size:.75rem">beneficiarios únicos</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card purple p-3 h-100">
            <div class="text-muted small mb-1"><i class="bi bi-person-check-fill me-1" style="color:#6f42c1"></i>Asignaciones</div>
            <div class="kpi-number" style="color:#6f42c1">{{ number_format($totalAsignaciones) }}</div>
            <div class="text-muted" style="font-size:.75rem">registros en servicios</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card orange p-3 h-100">
            <div class="text-muted small mb-1"><i class="bi bi-graph-up me-1" style="color:#fd7e14"></i>Promedio por servicio</div>
            <div class="kpi-number" style="color:#fd7e14">{{ number_format($promedioBenef, 1) }}</div>
            <div class="text-muted" style="font-size:.75rem">beneficiarios / servicio</div>
        </div>
    </div>
</div>

{{-- ── Tendencia ── --}}
<div class="chart-card p-3 mb-4">
    <div class="chart-title mb-3"><i class="bi bi-graph-up-arrow me-1"></i>Tendencia por período</div>
    <canvas id="chartTendencia" height="80"></canvas>
</div>

{{-- ── Fila 2: Por Rol + Por Sede ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-person-badge me-1"></i>Personas por rol</div>
            @if($porRol->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartRol"></canvas>
            @endif
        </div>
    </div>
    <div class="col-md-8">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-geo-alt-fill me-1"></i>Personas por sede</div>
            @if($porSede->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartSede"></canvas>
            @endif
        </div>
    </div>
</div>

{{-- ── Fila 3: Por Área + Por Tipo Actividad ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-diagram-3-fill me-1"></i>Servicios por área</div>
            @if($porArea->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartArea"></canvas>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-tags-fill me-1"></i>Servicios por tipo de actividad</div>
            @if($porTipoActividad->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartTipoAct"></canvas>
            @endif
        </div>
    </div>
</div>

{{-- ── Fila 4: Top Programas + Tipo Empleado + Top Dependencias ── --}}
<div class="row g-3 mb-2">
    <div class="col-md-5">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-mortarboard-fill me-1"></i>Top 5 programas académicos</div>
            @if($topProgramas->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartProgramas"></canvas>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-briefcase-fill me-1"></i>Empleados por tipo</div>
            @if($porTipoEmpleado->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartTipoEmp"></canvas>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card p-3 h-100">
            <div class="chart-title mb-3"><i class="bi bi-diagram-3 me-1"></i>Top 5 dependencias</div>
            @if($topDependencias->isEmpty())
                <p class="text-muted small text-center py-4">Sin datos</p>
            @else
                <canvas id="chartDependencias"></canvas>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ── Datos desde PHP ──
    const tendencia      = @json($tendencia);
    const porRol         = @json($porRol);
    const porSede        = @json($porSede);
    const porArea        = @json($porArea);
    const porTipoAct     = @json($porTipoActividad);
    const topProgramas   = @json($topProgramas);
    const porTipoEmp     = @json($porTipoEmpleado);
    const topDeps        = @json($topDependencias);

    // ── Paletas ──
    const GREEN   = '#196844';
    const TEAL    = '#20c997';
    const PURPLE  = '#6f42c1';
    const ORANGE  = '#fd7e14';
    const BLUE    = '#0d6efd';
    const RED     = '#dc3545';
    const DEPS    = '#FFFB75';

    const PALETTE = [GREEN, TEAL, PURPLE, ORANGE, BLUE, RED, '#ffc107', '#0dcaf0'];

    const ROL_COLORS = { 'Estudiante': TEAL, 'Graduado': PURPLE, 'Empleado': ORANGE };
    const TIPO_EMP_COLORS = { 'Docente': ORANGE, 'Administrativo': BLUE, 'Contratista': PURPLE };

    const baseFont = { family: 'system-ui, sans-serif', size: 12 };

    const tooltipDefaults = {
        backgroundColor: 'rgba(0,0,0,.75)',
        titleFont: { ...baseFont, weight: 'bold' },
        bodyFont: baseFont,
        padding: 10,
        cornerRadius: 6,
    };

    // ── Helper: asignar color por nombre ──
    function colorByName(map, name, fallbackIdx) {
        return map[name] ?? PALETTE[fallbackIdx % PALETTE.length];
    }

    // ── 1. Tendencia ──
    const ctxT = document.getElementById('chartTendencia');
    if (ctxT) {
        new Chart(ctxT, {
            type: 'line',
            data: {
                labels: tendencia.map(r => r.periodo),
                datasets: [
                    {
                        label: 'Servicios',
                        data: tendencia.map(r => r.servicios),
                        borderColor: GREEN,
                        backgroundColor: GREEN + '22',
                        fill: true,
                        tension: .35,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                    {
                        label: 'Asignaciones',
                        data: tendencia.map(r => r.asignaciones),
                        borderColor: TEAL,
                        backgroundColor: TEAL + '22',
                        fill: true,
                        tension: .35,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { tooltip: tooltipDefaults, legend: { position: 'top' } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    // ── 2. Por Rol (doughnut) ──
    const ctxRol = document.getElementById('chartRol');
    if (ctxRol && porRol.length) {
        new Chart(ctxRol, {
            type: 'doughnut',
            data: {
                labels: porRol.map(r => r.nombre),
                datasets: [{
                    data: porRol.map(r => r.total),
                    backgroundColor: porRol.map(r => colorByName(ROL_COLORS, r.nombre, 0)),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    tooltip: tooltipDefaults,
                    legend: { position: 'bottom', labels: { font: baseFont } },
                },
            },
        });
    }

    // ── 3. Por Sede (barras horizontales) ──
    const ctxSede = document.getElementById('chartSede');
    if (ctxSede && porSede.length) {
        new Chart(ctxSede, {
            type: 'bar',
            data: {
                labels: porSede.map(r => r.nombre),
                datasets: [{
                    label: 'Personas',
                    data: porSede.map(r => r.total),
                    backgroundColor: GREEN + 'cc',
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { tooltip: tooltipDefaults, legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    // ── 4. Por Área (barras verticales) ──
    const ctxArea = document.getElementById('chartArea');
    if (ctxArea && porArea.length) {
        new Chart(ctxArea, {
            type: 'bar',
            data: {
                labels: porArea.map(r => r.nombre),
                datasets: [{
                    label: 'Servicios',
                    data: porArea.map(r => r.total),
                    backgroundColor: porArea.map((_, i) => PALETTE[i % PALETTE.length] + 'cc'),
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                plugins: { tooltip: tooltipDefaults, legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    // ── 5. Por Tipo Actividad (doughnut) ──
    const ctxTA = document.getElementById('chartTipoAct');
    if (ctxTA && porTipoAct.length) {
        new Chart(ctxTA, {
            type: 'doughnut',
            data: {
                labels: porTipoAct.map(r => r.nombre),
                datasets: [{
                    data: porTipoAct.map(r => r.total),
                    backgroundColor: porTipoAct.map((_, i) => PALETTE[i % PALETTE.length]),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    tooltip: tooltipDefaults,
                    legend: { position: 'bottom', labels: { font: baseFont, boxWidth: 12 } },
                },
            },
        });
    }

    // ── 6. Top 5 Programas (barras horizontales) ──
    const ctxProg = document.getElementById('chartProgramas');
    if (ctxProg && topProgramas.length) {
        new Chart(ctxProg, {
            type: 'bar',
            data: {
                labels: topProgramas.map(r => r.nombre),
                datasets: [{
                    label: 'Personas',
                    data: topProgramas.map(r => r.total),
                    backgroundColor: TEAL + 'cc',
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { tooltip: tooltipDefaults, legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        });
    }

    // ── 7. Por Tipo Empleado (doughnut) ──
    const ctxTE = document.getElementById('chartTipoEmp');
    if (ctxTE && porTipoEmp.length) {
        new Chart(ctxTE, {
            type: 'doughnut',
            data: {
                labels: porTipoEmp.map(r => r.nombre),
                datasets: [{
                    data: porTipoEmp.map(r => r.total),
                    backgroundColor: porTipoEmp.map(r => colorByName(TIPO_EMP_COLORS, r.nombre, 0)),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    tooltip: tooltipDefaults,
                    legend: { position: 'bottom', labels: { font: baseFont, boxWidth: 12 } },
                },
            },
        });
    }

    // ── 8. Top 5 Dependencias (barras horizontales) ──
    const ctxDep = document.getElementById('chartDependencias');
    if (ctxDep && topDeps.length) {
        new Chart(ctxDep, {
            type: 'bar',
            data: {
                labels: topDeps.map(r => r.nombre),
                datasets: [{
                    label: 'Personas',
                    data: topDeps.map(r => r.total),
                    backgroundColor: DEPS + 'cc',
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { tooltip: tooltipDefaults, legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        });
    }
})();
</script>
@endpush

</x-layout>

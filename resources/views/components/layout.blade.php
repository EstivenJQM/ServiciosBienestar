@props(['title' => config('app.name')])

@php
    $seccionActiva = match(true) {
        request()->routeIs('areas.*', 'componentes.*', 'lineas.*', 'tipo-actividad.*') => 'caracterizacion',
        request()->routeIs('sedes.*', 'facultades.*', 'programas.*', 'dependencias.*', 'cargos.*') => 'academico',
        request()->routeIs('servicios.*', 'periodos.*') => 'servicios',
        request()->routeIs('usuarios.*')   => 'usuarios',
        default => null,
    };
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sibi-green: #196844; }

        /* ── Top navbar ── */
        .navbar-top {
            background-color: var(--sibi-green);
        }
        .navbar-top .nav-link {
            color: rgba(255,255,255,.8);
            font-weight: 500;
            padding: .5rem 1.2rem;
            border-radius: .375rem;
            transition: background .15s, color .15s;
        }
        .navbar-top .nav-link:hover,
        .navbar-top .nav-link.active {
            background: rgba(255,255,255,.15);
            color: #fff;
        }
        .navbar-top .navbar-brand {
            color: #fff;
            font-weight: 700;
            letter-spacing: .5px;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: 230px;
            min-width: 230px;
            min-height: calc(100vh - 56px);
            background: #fff;
            border-right: 1px solid #e5e7eb;
        }
        .sidebar-heading {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #9ca3af;
            padding: 1.25rem 1rem .4rem;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .5rem 1rem;
            color: #374151;
            text-decoration: none;
            font-size: .9rem;
            border-radius: .375rem;
            margin: 1px .5rem;
            transition: background .15s, color .15s;
        }
        .sidebar-link:hover {
            background: #f3f4f6;
            color: var(--sibi-green);
        }
        .sidebar-link.active {
            background: #e6f2ec;
            color: var(--sibi-green);
            font-weight: 600;
        }
        .sidebar-link .bi {
            font-size: 1rem;
            opacity: .75;
        }
        .sidebar-link.active .bi {
            opacity: 1;
        }

        /* ── Layout body ── */
        #page-wrapper {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        #main-content {
            flex: 1;
            padding: 1.75rem;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        /* ── Botón verde SIBI ── */
        .btn-sibi {
            background-color: var(--sibi-green);
            border-color: var(--sibi-green);
            color: #fff;
        }
        .btn-sibi:hover, .btn-sibi:focus, .btn-sibi:active {
            background-color: #145a39;
            border-color: #145a39;
            color: #fff;
        }
        .btn-outline-sibi {
            color: var(--sibi-green);
            border-color: var(--sibi-green);
        }
        .btn-outline-sibi:hover {
            background-color: var(--sibi-green);
            border-color: var(--sibi-green);
            color: #fff;
        }

        /* ── Tree colors ── */
        .tree { --indent: 1.4rem; }
        .tree-area  { border-left: 3px solid #196844; }
        .tree-comp  { border-left: 3px solid #6f42c1; margin-left: var(--indent); }
        .tree-linea { border-left: 3px solid #20c997; margin-left: calc(var(--indent) * 2); }
    </style>
</head>
<body class="bg-light">

{{-- ═══════════════════ TOP NAVBAR ═══════════════════ --}}
<nav class="navbar navbar-top navbar-expand-lg px-3 sticky-top" style="height:56px">
    <a class="navbar-brand me-4" href="/">SIBI</a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#topMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-center" id="topMenu">
        <ul class="navbar-nav gap-1">
            <li class="nav-item">
                <a class="nav-link {{ $seccionActiva === 'caracterizacion' ? 'active' : '' }}"
                   href="{{ route('areas.index') }}">
                    <i class="bi bi-puzzle-fill me-1"></i>Caracterización
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $seccionActiva === 'servicios' ? 'active' : '' }}"
                   href="{{ route('servicios.index') }}">
                    <i class="bi bi-clipboard2-check-fill me-1"></i>Servicios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $seccionActiva === 'academico' ? 'active' : '' }}"
                   href="{{ route('sedes.index') }}">
                    <i class="bi bi-mortarboard me-1"></i>Académico
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $seccionActiva === 'usuarios' ? 'active' : '' }}"
                   href="{{ route('usuarios.index') }}">
                    <i class="bi bi-people me-1"></i>Usuarios
                </a>
            </li>
        </ul>
    </div>
</nav>

{{-- ═══════════════════ BODY: SIDEBAR + CONTENT ═══════════════════ --}}
<div id="page-wrapper">

    {{-- ── Sidebar ── --}}
    <aside id="sidebar">

        @if($seccionActiva === 'caracterizacion')
            <p class="sidebar-heading">Caracterización</p>

            <a href="{{ route('areas.index') }}"
               class="sidebar-link {{ request()->routeIs('areas.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Áreas
            </a>
            <a href="{{ route('componentes.index') }}"
               class="sidebar-link {{ request()->routeIs('componentes.*') ? 'active' : '' }}">
                <i class="bi bi-collection-fill"></i> Componentes
            </a>
            <a href="{{ route('lineas.index') }}"
               class="sidebar-link {{ request()->routeIs('lineas.*') ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> Líneas
            </a>
            <a href="{{ route('tipo-actividad.index') }}"
               class="sidebar-link {{ request()->routeIs('tipo-actividad.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> Tipos de Actividad
            </a>

        @elseif($seccionActiva === 'servicios')
            <p class="sidebar-heading">Servicios</p>
            <a href="{{ route('periodos.index') }}"
               class="sidebar-link {{ request()->routeIs('periodos.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Períodos
            </a>
            <a href="{{ route('servicios.index') }}"
               class="sidebar-link {{ request()->routeIs('servicios.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-heart-fill"></i> Servicios
            </a>

        @elseif($seccionActiva === 'academico')
            <p class="sidebar-heading">Académico</p>
            <a href="{{ route('sedes.index') }}"
               class="sidebar-link {{ request()->routeIs('sedes.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i> Sedes
            </a>
            <a href="{{ route('facultades.index') }}"
               class="sidebar-link {{ request()->routeIs('facultades.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Facultades
            </a>
            <a href="{{ route('programas.index') }}"
               class="sidebar-link {{ request()->routeIs('programas.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark-fill"></i> Programas
            </a>
            <a href="{{ route('dependencias.index') }}"
               class="sidebar-link {{ request()->routeIs('dependencias.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Dependencias
            </a>
            <a href="{{ route('cargos.index') }}"
               class="sidebar-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i> Cargos
            </a>

        @elseif($seccionActiva === 'usuarios')
            <p class="sidebar-heading">Usuarios</p>
            <a href="{{ route('usuarios.index') }}"
               class="sidebar-link {{ request()->routeIs('usuarios.index') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Lista de Usuarios
            </a>
            <a href="{{ route('usuarios.carga.index') }}"
               class="sidebar-link {{ request()->routeIs('usuarios.carga.*') ? 'active' : '' }}">
                <i class="bi bi-person-fill-up"></i> Carga de Usuarios
            </a>
            @php $totalInc = \App\Models\CargaInconsistencia::count(); @endphp
            <a href="{{ route('usuarios.inconsistencias.index') }}"
               class="sidebar-link {{ request()->routeIs('usuarios.inconsistencias.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle-fill"></i> Inconsistencias
                @if($totalInc > 0)
                    <span class="badge bg-danger ms-auto" style="font-size:.6rem">{{ $totalInc }}</span>
                @endif
            </a>

        @else
            <p class="sidebar-heading">Menú</p>
            <span class="sidebar-link text-muted">Selecciona una sección.</span>
        @endif

    </aside>

    {{-- ── Main content ── --}}
    <main id="main-content">
        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-alert type="danger" :message="session('error')" />
        @endif

        {{ $slot }}
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'UPDS Cobros')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/estilos.css') }}" rel="stylesheet">
</head>
<body>
    @auth
        <nav class="navbar navbar-expand-lg barra-superior">
            <div class="container">
                <span class="navbar-brand">
                    <img src="{{ asset('images/descarga.png') }}" alt="UPDS">
                    UPDS Cobros
                </span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="navbar-nav me-auto ms-lg-3">
                        @if(auth()->user()->esSuperAdmin())
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.usuarios.*') ? 'active' : '' }}" href="{{ route('superadmin.usuarios.index') }}">Usuarios</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.carreras.*') ? 'active' : '' }}" href="{{ route('superadmin.carreras.index') }}">Carreras</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.estudiantes.*') ? 'active' : '' }}" href="{{ route('superadmin.estudiantes.index') }}">Estudiantes</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.backups.*') ? 'active' : '' }}" href="{{ route('superadmin.backups.index') }}">Backups</a></li>
                        @endif
                        @if(auth()->user()->esSuperAdmin() || auth()->user()->esAdmin())
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.cajas.*') ? 'active' : '' }}" href="{{ route('admin.cajas.index') }}">Cajas</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.items.*') ? 'active' : '' }}" href="{{ route('admin.items.index') }}">Items</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reportes.*') ? 'active' : '' }}" href="{{ route('admin.reportes.index') }}">Reporte</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reimpresiones.*') ? 'active' : '' }}" href="{{ route('admin.reimpresiones.index') }}">Reimpresiones</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.devoluciones.*') ? 'active' : '' }}" href="{{ route('admin.devoluciones.index') }}">Devoluciones</a></li>
                        @endif
                        @if(auth()->user()->esCajero())
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('cajero.cobros.*') ? 'active' : '' }}" href="{{ route('cajero.cobros.create') }}">Registrar cobro</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('cajero.reimpresion.*') ? 'active' : '' }}" href="{{ route('cajero.reimpresion.mis_solicitudes') }}">Mis reimpresiones</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('cajero.devolucion.*') ? 'active' : '' }}" href="{{ route('cajero.devolucion.mis_solicitudes') }}">Mis devoluciones</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('cajero.caja.*') ? 'active' : '' }}" href="{{ route('cajero.caja.cerrar') }}">Cerrar turno / caja</a></li>
                        @endif
                    </ul>
                    <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                        <div class="usuario-actual">
                            <span class="avatar">{{ strtoupper(substr(auth()->user()->persona->nombreCompleto(), 0, 1)) }}</span>
                            <span>{{ auth()->user()->persona->nombreCompleto() }}</span>
                        </div>
                        @if(auth()->user()->esCajero() && auth()->user()->arqueoAbierto())
                            <span class="text-muted small">{{ auth()->user()->arqueoAbierto()->caja->nombre }} — cierra tu turno para salir</span>
                        @else
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm" type="submit">Cerrar sesión</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <div class="container contenido mb-5">
        @if(session('mensaje'))
            <div class="alert alert-success">{{ session('mensaje') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('contenido')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

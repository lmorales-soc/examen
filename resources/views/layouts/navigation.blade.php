<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">
            {{ config('app.name', 'Sistema de Vacaciones') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @if(auth()->user()?->hasRole(['HR_MANAGER', 'ADMIN']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                @endif
                @if(auth()->user()?->hasRole(['HR_MANAGER', 'ADMIN']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">{{ __('Empleados') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('areas.*') ? 'active' : '' }}" href="{{ route('areas.index') }}">{{ __('Áreas') }}</a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vacation-requests.*') ? 'active' : '' }}" href="{{ route('vacation-requests.index') }}">{{ __('Solicitudes') }}</a>
                </li>
                @if(auth()->user()?->hasRole(['AREA_MANAGER', 'HR_MANAGER', 'ADMIN']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}" href="{{ route('approvals.index') }}">{{ __('Aprobaciones') }}</a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                        {{ __('Notificaciones') }}
                        @auth
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="badge bg-danger rounded-pill">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        @endauth
                    </a>
                </li>
            </ul>
            @auth
            @php
                $myManagedAreas = \App\Models\Area::where('manager_user_id', auth()->id())->pluck('name');
            @endphp
            @if($myManagedAreas->isNotEmpty())
                <li class="nav-item">
                    <span class="nav-link py-2" title="Áreas que usted gerencia">
                        <span class="badge bg-light text-dark border">Soy gerente de: {{ $myManagedAreas->join(', ') }}</span>
                    </span>
                </li>
            @endif
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-1">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Perfil') }}</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    {{ __('Cerrar sesión') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>

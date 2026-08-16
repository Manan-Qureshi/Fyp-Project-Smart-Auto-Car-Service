<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Auto Car Service</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/font-awesome/all.min.css') }}">
    <link href="{{ asset('css/glass.css') }}" rel="stylesheet">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</head>
<body>
<div id="app">
    @auth
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-car-side me-2"></i> Smart Auto Car Service
        </div>

        {{-- Hide generic Dashboard for admin; they have their own below --}}
        @if(!auth()->user()->isAdmin())
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        @endif

        {{-- ADMIN --}}
        @if(auth()->user()->isAdmin())
            <div class="sidebar-heading mt-3 mb-2 text-muted text-uppercase fw-bold px-3" style="font-size:.7rem">Admin</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="{{ '/admin/providers' }}" class="nav-link {{ request()->routeIs('admin/providers*') ? 'active' : '' }}">
                <i class="fas fa-store"></i> Service Providers
            </a>
            <a href="{{ '/admin/services' }}" class="nav-link {{ request()->routeIs('admin/services*') ? 'active' : '' }}">
                <i class="fas fa-concierge-bell"></i> Services
            </a>
            <a href="{{ '/admin/cars' }}" class="nav-link {{ request()->routeIs('admin/cars*') ? 'active' : '' }}">
                <i class="fas fa-car"></i> Car Types
            </a>
        @endif

        {{-- PROVIDER --}}
        @if(auth()->user()->isProvider())
            <div class="sidebar-heading mt-3 mb-2 text-muted text-uppercase fw-bold px-3" style="font-size:.7rem">Provider</div>
            <a href="{{ route('provider.dashboard') }}" class="nav-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> My Dashboard
            </a>
            <a href="{{ route('provider.workers.index') }}" class="nav-link {{ request()->routeIs('provider.workers.*') ? 'active' : '' }}">
                <i class="fas fa-hard-hat"></i> Workers
            </a>
            <a href="{{ route('provider.services.index') }}" class="nav-link {{ request()->routeIs('provider.services.*') ? 'active' : '' }}">
                <i class="fas fa-tools"></i> My Services
            </a>
        @endif

        {{-- CUSTOMER --}}
        @if(auth()->user()->isCustomer())
            <a href="{{ route('welcome') }}" class="nav-link">
                <i class="fas fa-map-marker-alt"></i> Find Providers
            </a>
        @endif

        <div class="mt-auto">
            <a href="{{ route('profile.edit') }}" class="nav-link">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="{{ route('logout') }}" class="nav-link text-danger"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
    @endauth

    <div class="{{ auth()->check() ? 'main-content' : '' }} {{ (request()->is('login') || request()->is('register')) ? 'auth-mode' : '' }}">
        @if(!request()->is('login') && !request()->is('register'))
        <div class="glass-header justify-content-between">
            <div>
                @auth
                    <h4 class="m-0 fw-semibold">Welcome, {{ Auth::user()->name }}</h4>
                @else
                    <a class="navbar-brand text-white fw-bold" href="{{ url('/') }}">
                        <i class="fas fa-car-side me-2"></i> {{ config('app.name') }}
                    </a>
                @endauth
            </div>
            <div class="d-flex align-items-center gap-2">
                @guest
                    @if(Route::has('login'))
                        <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Login</a>
                    @endif
                    @if(Route::has('register'))
                        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Register</a>
                    @endif
                @else
                    <div class="dropdown">
                        <button class="btn btn-link text-dark text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=667eea&color=fff"
                                 class="rounded-circle me-1" width="32" height="32">
                            {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end glass-card border-0">
                            <li><span class="dropdown-item-text text-muted small">{{ ucfirst(Auth::user()->role) }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>
        @endif

        <main class="py-4">
            <div class="container-fluid px-4">
                @foreach(['success','error','status','info','warning'] as $type)
                    @if(session($type))
                        <div class="alert alert-{{ $type === 'error' ? 'danger' : ($type === 'status' ? 'info' : $type) }} alert-dismissible fade show shadow-sm border-0">
                            <i class="fas fa-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
                            {{ session($type) }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                @endforeach
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
@stack('scripts')
</body>
</html>
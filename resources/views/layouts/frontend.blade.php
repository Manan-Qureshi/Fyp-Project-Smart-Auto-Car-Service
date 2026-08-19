<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Auto Car Service</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/font-awesome/all.min.css') }}">

    <!-- Custom CSS -->
    <link href="{{ asset('css/glass.css') }}" rel="stylesheet">

</head>

<body class="d-flex flex-column min-vh-100" style="background:#f7f9fc;">

    <!-- Navbar -->
    <nav class="smart-navbar">
        <div class="navbar-narrow d-flex align-items-center justify-content-between">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-car-side"></i>
                Smart Auto Car Service
            </a>

            <!-- Toggler for mobile -->
            <button class="navbar-toggler border-0 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#frontendNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav links — always visible on desktop -->
            <div class="d-md-flex align-items-center gap-1 collapse navbar-collapse justify-content-end" id="frontendNav" style="flex: unset;">
                <a class="nav-link {{ request()->is('/') ? 'active-link' : '' }}" href="{{ url('/') }}">Home</a>
                <a class="nav-link" href="{{ url('/#providersSection') }}" id="navProvidersLink">Providers</a>
                <a class="nav-link" href="#smartFooter" id="navContactLink">Contact</a>

                @guest
                    <a class="btn-nav-outline ms-2" href="{{ route('login') }}">Login</a>
                    <a class="btn-nav-solid ms-2" href="{{ route('register') }}">Register</a>
                @else
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active-link' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow-1">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="smart-footer" id="smartFooter">
        <div class="footer-narrow">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-car-side" style="font-size:1.3rem;color:#fff;"></i>
                        <span class="footer-brand-name">Smart Auto Car Service</span>
                    </div>
                    <p class="footer-desc">Your trusted partner for premium auto care. We make vehicle maintenance convenient, reliable, and affordable.</p>
                    <div class="mt-3">
                        <a href="#" class="footer-social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="footer-social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="footer-social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Account Column -->
                <div class="col-lg-2 col-md-6 offset-lg-1">
                    <h5 class="footer-heading">Account</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('profile.edit') }}">Profile</a></li>
                        <li><a href="#">Settings</a></li>
                        <li><a href="#">Billing</a></li>
                        <li><a href="#">Notifications</a></li>
                    </ul>
                </div>

                <!-- About Column -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-heading">About</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('services.index') }}">Services</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>

                <!-- Company Column -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading">Company</h5>
                    <ul class="footer-links">
                        <li><a href="#">Community</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer-bottom">
                <p class="footer-bottom-text mb-0">&copy; {{ date('Y') }} Smart Auto Car Service. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Terms</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to Contact/Footer
            const contactBtn = document.getElementById('navContactLink');
            if (contactBtn) {
                contactBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const footer = document.getElementById('smartFooter');
                    if (footer) {
                        footer.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }

            // Smooth scroll to Providers Section
            const providersBtn = document.getElementById('navProvidersLink');
            if (providersBtn) {
                providersBtn.addEventListener('click', function(e) {
                    if (window.location.pathname === '/' || window.location.pathname === '') {
                        e.preventDefault();
                        const sec = document.getElementById('providersSection');
                        if (sec) {
                            sec.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            }
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>
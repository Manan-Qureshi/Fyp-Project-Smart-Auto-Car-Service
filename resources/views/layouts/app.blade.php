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
            <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="fas fa-concierge-bell"></i> Services
            </a>
            <a href="{{ '/admin/cars' }}" class="nav-link {{ request()->routeIs('admin/cars*') ? 'active' : '' }}">
                <i class="fas fa-car"></i> Car Types
            </a>
            <a href="{{ route('admin.financial') }}" class="nav-link {{ request()->routeIs('admin.financial') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Payments
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
                    {{-- NOTIFICATION BELL --}}
                    @auth
                    <div class="dropdown me-2" id="notif-bell-wrapper">
                        <button class="btn btn-link text-dark p-1 position-relative" type="button" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                            <i class="fas fa-bell fs-5"></i>
                            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;display:none;">0</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end glass-card border-0 shadow p-0" id="notif-dropdown" style="min-width:320px;max-height:400px;overflow-y:auto;">
                            <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small">Notifications</span>
                                <a href="#" id="notif-mark-all-read" class="text-primary small text-decoration-none" style="display:none;">Mark all read</a>
                            </li>
                            <li id="notif-empty" class="px-3 py-3 text-center text-muted small">No new notifications</li>
                        </ul>
                    </div>
                    @endauth

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
                        <div class="auto-alert alert alert-{{ $type === 'error' ? 'danger' : ($type === 'status' ? 'info' : $type) }} fade show shadow-sm border-0">
                            <i class="fas fa-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
                            {{ session($type) }}
                        </div>
                    @endif
                @endforeach
                @if ($errors->any())
                    <div class="auto-alert alert alert-danger alert-dismissible fade show shadow-sm border-0">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
<script>
    // Auto-dismiss session flash alerts after 4 seconds using jQuery
    $(document).ready(function () {
        setTimeout(function () {
            $('.auto-alert').fadeOut('slow', function () {
                $(this).remove();
            });
        }, 4000);
    });
</script>

@auth
<script>
(function () {
    var fetchUrl   = "{{ route('notifications.fetch') }}";
    var markUrl    = "{{ route('notifications.markRead') }}";
    var csrfToken  = "{{ csrf_token() }}";
    var badge      = document.getElementById('notif-badge');
    var dropdown   = document.getElementById('notif-dropdown');
    var emptyMsg   = document.getElementById('notif-empty');
    var markAllBtn = document.getElementById('notif-mark-all-read');

    if (!badge) return; // Not logged in or bell not present

    var lastKnownCount = null;
    var seenNotifIds = new Set();
    var isFirstLoad = true;

    // Web Audio API Ringtone Alert Generator
    var audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    // Unlock Audio Context on user interaction
    function unlockAudio() {
        getAudioContext();
        window.removeEventListener('click', unlockAudio);
        window.removeEventListener('keydown', unlockAudio);
        window.removeEventListener('touchstart', unlockAudio);
    }
    window.addEventListener('click', unlockAudio);
    window.addEventListener('keydown', unlockAudio);
    window.addEventListener('touchstart', unlockAudio);

    function playNotificationRingtone() {
        try {
            var ctx = getAudioContext();
            if (!ctx) return;

            var now = ctx.currentTime;

            // Tone 1 (High chime: 659.25Hz - E5)
            var osc1 = ctx.createOscillator();
            var gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.2, now);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Tone 2 (Brighter chime: 880Hz - A5)
            var osc2 = ctx.createOscillator();
            var gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.12);
            gain2.gain.setValueAtTime(0.25, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.6);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.6);

            // Tone 3 (Final pleasant echo tone: 1046.50Hz - C6)
            var osc3 = ctx.createOscillator();
            var gain3 = ctx.createGain();
            osc3.type = 'sine';
            osc3.frequency.setValueAtTime(1046.50, now + 0.25);
            gain3.gain.setValueAtTime(0.2, now + 0.25);
            gain3.gain.exponentialRampToValueAtTime(0.0001, now + 0.75);
            osc3.connect(gain3);
            gain3.connect(ctx.destination);
            osc3.start(now + 0.25);
            osc3.stop(now + 0.75);
        } catch (e) {
            console.warn('Notification audio play blocked or error:', e);
        }
    }

    function colorClass(color) {
        var map = { success:'text-success', danger:'text-danger', warning:'text-warning', info:'text-info', primary:'text-primary' };
        return map[color] || 'text-secondary';
    }

    function showToastAlert(title, message, icon, color) {
        var toastContainer = document.getElementById('notif-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'notif-toast-container';
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        var toastEl = document.createElement('div');
        toastEl.className = 'toast show align-items-center text-white bg-dark border-0 rounded-4 shadow-lg mb-2';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = '<div class="d-flex p-3">' +
            '<div class="toast-body d-flex align-items-center gap-2">' +
                '<i class="fas ' + (icon || 'fa-bell') + ' fa-lg ' + colorClass(color) + '"></i>' +
                '<div>' +
                    '<div class="fw-bold small">' + (title || 'New Notification') + '</div>' +
                    '<div class="text-white-50 small">' + (message || '') + '</div>' +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" onclick="this.closest(\'.toast\').remove()"></button>' +
        '</div>';

        toastContainer.appendChild(toastEl);
        setTimeout(function () {
            if (toastEl && toastEl.parentNode) {
                toastEl.classList.remove('show');
                setTimeout(function () { if (toastEl && toastEl.parentNode) toastEl.remove(); }, 300);
            }
        }, 6000);
    }

    function loadNotifications() {
        fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                var count = data.count || 0;
                var newlyReceivedNotifs = [];

                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(function(n) {
                        if (!seenNotifIds.has(n.id)) {
                            seenNotifIds.add(n.id);
                            if (!isFirstLoad) {
                                newlyReceivedNotifs.push(n);
                            }
                        }
                    });
                }

                // If new notifications arrived after initial page load
                if (!isFirstLoad && (newlyReceivedNotifs.length > 0 || (lastKnownCount !== null && count > lastKnownCount))) {
                    playNotificationRingtone();
                    if (newlyReceivedNotifs.length > 0) {
                        var latest = newlyReceivedNotifs[0];
                        showToastAlert(latest.data.title, latest.data.message, latest.data.icon, latest.data.color);
                    }
                }

                lastKnownCount = count;
                isFirstLoad = false;

                // Update badge
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = '';
                    if (markAllBtn) markAllBtn.style.display = '';
                } else {
                    badge.style.display = 'none';
                    if (markAllBtn) markAllBtn.style.display = 'none';
                }

                // Remove old items (keep header li and empty li)
                dropdown.querySelectorAll('.notif-item').forEach(function(el){ el.remove(); });

                if (data.notifications && data.notifications.length > 0) {
                    if (emptyMsg) emptyMsg.style.display = 'none';
                    data.notifications.forEach(function(n) {
                        var d = n.data;
                        var li = document.createElement('li');
                        li.className = 'notif-item border-bottom px-3 py-2';
                        li.innerHTML = '<div class="d-flex align-items-start gap-2">' +
                            '<i class="fas ' + (d.icon || 'fa-bell') + ' mt-1 ' + colorClass(d.color) + '"></i>' +
                            '<div class="flex-grow-1">' +
                                '<div class="fw-semibold small">' + (d.title || 'Notification') + '</div>' +
                                '<div class="text-muted" style="font-size:.78rem;">' + (d.message || '') + '</div>' +
                                '<div class="text-muted" style="font-size:.7rem;">' + (n.created || '') + '</div>' +
                            '</div>' +
                        '</div>';
                        dropdown.appendChild(li);
                    });
                } else {
                    if (emptyMsg) emptyMsg.style.display = '';
                }
            })
            .catch(function(){});
    }

    // Mark all read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch(markUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(){ loadNotifications(); });
        });
    }

    loadNotifications();
    setInterval(loadNotifications, 5000);
})();
</script>
@endauth
</body>
</html>
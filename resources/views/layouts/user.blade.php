<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'User Dashboard - SofaHub')</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/usercss/layout.css') }}">

    <style>
        /* ===== Impersonation Banner ===== */
        .impersonation-banner {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 2000;
            background-color: #ffc107;
            color: #212529;
            padding: 10px 15px;
            font-weight: 500;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 320px;
            font-size: 0.9rem;
        }

        .impersonation-banner button {
            font-size: 0.75rem;
            padding: 3px 8px;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            width: 220px;
            background: #2c3e50;
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 70px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.active {
            left: -220px;
        }

        .sidebar-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 70px;
            background: #1a252f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .logo-icon {
            margin-right: 5px;
        }

        .sidebar-menu {
            margin-top: 20px;
        }

        .sidebar-menu .nav-link {
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
        }

        .sidebar-menu .nav-link.active,
        .sidebar-menu .nav-link:hover {
            background: #34495e;
            border-radius: 4px;
        }

        .main-content {
            margin-left: 220px;
            padding: 70px 20px 20px 20px;
            transition: all 0.3s ease;
        }

        .sidebar.active~.main-content {
            margin-left: 0;
        }

        .top-nav {
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            height: 70px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid #ddd;
            z-index: 1001;
            transition: all 0.3s ease;
        }

        .sidebar.active~.top-nav {
            left: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: #3498db;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
        }

        main {
            margin-top: 20px;
        }

        /* Alerts */
        .alert {
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -220px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 70px 10px 10px 10px;
            }

            .top-nav {
                left: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- ===== Impersonation Banner ===== --}}
    @if(session()->has('impersonator_id'))
    <div class="impersonation-banner">
        ⚠️ You are impersonating <strong>{{ auth()->user()->name }}</strong>
        <form action="{{ route('admin.leave-impersonation') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-dark">Exit</button>
        </form>
    </div>
    @endif

    {{-- ===== Sidebar ===== --}}
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-couch logo-icon"></i>
                <span class="logo-text">Sofa<span>Hub</span></span>
            </div>
        </div>

        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <a href="{{ url('user/dashboard') }}" class="nav-link {{ request()->is('user/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ url('user/sales') }}" class="nav-link {{ request()->is('user/sales') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i> Sales
                </a>
                <a href="{{ url('user/invoices') }}" class="nav-link {{ request()->is('user/invoices') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i> Invoices
                </a>
                <a href="{{ url('user/products') }}" class="nav-link {{ request()->is('user/products') ? 'active' : '' }}">
                    <i class="fas fa-couch"></i> Products
                </a>
                <a href="{{ url('user/purchase') }}" class="nav-link {{ request()->is('user/purchase') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> Purchase
                </a>
                <a href="{{ url('user/dispatch') }}" class="nav-link {{ request()->is('user/dispatch') ? 'active' : '' }}">
                    <i class="fas fa-shipping-fast"></i> Dispatch
                </a>
            </nav>
        </div>
    </div>

    {{-- ===== Main Content ===== --}}
    <div class="main-content">

        {{-- Top Navigation --}}
        <div class="top-nav">
            <button class="menu-toggle btn btn-link" type="button"
                onclick="document.querySelector('.sidebar').classList.toggle('active')">
                <i class="fas fa-bars"></i>
            </button>

            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false"></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ url('user/profile') }}">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <main>
            @if(session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>

    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Sidebar toggle for mobile
        document.querySelectorAll('.menu-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        });
    </script>

    @stack('scripts')

</body>

</html>

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
    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-couch logo-icon"></i>
                <span class="logo-text">Sofa<span>Hub</span></span>
            </div>
        </div>

        <div class="sidebar-menu">
            <nav class="nav flex-column">

                <a href="{{ url('user/dashboard') }}"
                    class="nav-link {{ request()->is('user/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ url('user/sales') }}" class="nav-link {{ request()->is('user/sales') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Sales</span>
                </a>

                <a href="{{ url('user/invoices') }}"
                    class="nav-link {{ request()->is('user/invoices') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Invoices</span>
                </a>

                <a href="{{ url('user/products') }}"
                    class="nav-link {{ request()->is('user/products') ? 'active' : '' }}">
                    <i class="fas fa-couch"></i>
                    <span>Products</span>
                </a>

                <a href="{{ url('user/purchase') }}"
                    class="nav-link {{ request()->is('user/purchase') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Purchase</span>
                </a>

                <a href="{{ url('user/dispatch') }}"
                    class="nav-link {{ request()->is('user/dispatch') ? 'active' : '' }}">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Dispatch</span>
                </a>

            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Top Navbar -->
        <div class="top-nav d-flex justify-content-between align-items-center">

            <div class="search-bar">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="user-info d-flex align-items-center gap-2">

                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>

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

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

    </div>

    <!-- JS (ONLY ONCE, BEFORE </body>) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
   <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin Dashboard | SofaHub')</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">  
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>  window.csrfToken = '{{ csrf_token() }}';</script>


    <link rel="stylesheet" href="{{asset('css/admincss/layout.css')}}" />

  

    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="{{ asset('assets/img/logo.png') }}" alt="SofaHub Logo" />
            <h2>SofaHub Admin</h2>
        </div>
        <ul>
            <li>
                <a href="{{ url('admin/dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('admin/products') }}" class="{{ request()->is('admin/products') ? 'active' : '' }}">
                    <i class="fas fa-couch"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="{{ url('admin/rawmaterials') }}"
                    class="{{ request()->is('admin/rawmaterials') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i>
                    <span>Raw Materials</span>
                </a>
            </li>
            <li>
                <a href="{{ url('admin/production') }}" class="{{ request()->is('admin/production') ? 'active' : '' }}">
                    <i class="fas fa-industry"></i>
                    <span>Production</span>
                </a>
            </li>
            <li>
                <a href="{{ url('admin/invoice') }}" class="{{ request()->is('admin/invoice') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Invoices</span>
                </a>
            </li>
            <li>
                <a href="{{ url('admin/dispatch') }}" class="{{ request()->is('admin/dispatch') ? 'active' : '' }}">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Dispatch</span>
                </a>
            </li>

            <li>
                <a href="{{ url('admin/viewaccount') }}" class="{{ request()->is('admin/viewaccount') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Accounts
        </a>
            </li>




        </ul>
    </div>

    <!-- Navbar -->
    <div class="navbar">
        <div class="menu-toggle">☰</div>
        <h1>@yield('page-title', 'Admin Dashboard')</h1>
        <div class="user">
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ url('admin/profile') }}"><i class="fas fa-user"></i> Profile</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>

                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')

        <div class="footer">
            <p>© {{ date('Y') }} SofaHub | Admin Panel | Developed by NW Tech</p>
        </div>
    </div>

    <!-- JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });

        // Add active class to current page
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar a');

        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });

        // Handle logout form submission
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            const logoutBtn = logoutForm.querySelector('.logout-btn');
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    logoutForm.submit();
                }
            });
        }
    });
    </script>

    @stack('scripts')
</body>

</html>
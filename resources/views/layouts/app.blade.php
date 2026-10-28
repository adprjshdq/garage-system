<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — MotoCare Workshop Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --mc-primary: #1e3a8a;
            --mc-primary-dark: #172554;
            --mc-primary-light: #3b82f6;
            --mc-accent: #f97316;
            --mc-accent-hover: #ea580c;
            --mc-bg: #f8fafc;
            --mc-card-bg: #ffffff;
            --mc-text-main: #0f172a;
            --mc-text-muted: #64748b;
            --mc-border: #e2e8f0;
            --sidebar-width: 260px;
        }

        body {
            background-color: var(--mc-bg);
            color: var(--mc-text-main);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #ffffff;
            color: var(--mc-text-main);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            transition: all 0.25s ease-in-out;
            overflow-y: auto;
            border-right: 1px solid var(--mc-border);
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            background: #ffffff;
            border-bottom: 1px solid var(--mc-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, var(--mc-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }

        .sidebar-brand .brand-text {
            line-height: 1.2;
        }

        .sidebar-brand .brand-text .fw-bold {
            color: var(--mc-text-main) !important;
        }

        .sidebar-brand .brand-text .text-secondary {
            color: var(--mc-text-muted) !important;
        }

        .sidebar-heading {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--mc-text-muted);
            font-weight: 700;
            padding: 1.2rem 1.5rem 0.4rem 1.5rem;
            margin: 0;
        }

        .nav-sidebar {
            padding: 0.5rem 0.75rem 2rem 0.75rem;
        }

        .nav-sidebar .nav-link {
            color: #475569;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.58rem 0.85rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.15s ease-in-out;
            margin-bottom: 0.2rem;
            text-decoration: none;
        }

        .nav-sidebar .nav-link i {
            font-size: 1.1rem;
            color: #64748b;
            transition: color 0.15s ease;
        }

        .nav-sidebar .nav-link:hover {
            color: var(--mc-primary);
            background: #eff6ff;
        }

        .nav-sidebar .nav-link:hover i {
            color: var(--mc-primary-light);
        }

        .nav-sidebar .nav-link.active {
            color: #ffffff;
            background: var(--mc-primary);
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.25);
        }

        .nav-sidebar .nav-link.active i {
            color: #93c5fd;
        }

        /* Main Content wrapper */
        #content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease-in-out;
        }

        /* Topbar */
        #topbar {
            height: 66px;
            background: #ffffff;
            border-bottom: 1px solid var(--mc-border);
            padding: 0 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        .main-container {
            padding: 1.75rem;
            flex: 1;
        }

        /* Cards & Components */
        .card-custom {
            background: var(--mc-card-bg);
            border: 1px solid var(--mc-border);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }

        .stat-card {
            border-radius: 0.75rem;
            border: 1px solid var(--mc-border);
            background: #ffffff;
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .stat-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .btn-mc-primary {
            background-color: var(--mc-primary);
            border-color: var(--mc-primary);
            color: #ffffff;
            font-weight: 500;
        }

        .btn-mc-primary:hover {
            background-color: var(--mc-primary-dark);
            border-color: var(--mc-primary-dark);
            color: #ffffff;
        }

        .btn-mc-accent {
            background-color: var(--mc-accent);
            border-color: var(--mc-accent);
            color: #ffffff;
            font-weight: 500;
        }

        .btn-mc-accent:hover {
            background-color: var(--mc-accent-hover);
            border-color: var(--mc-accent-hover);
            color: #ffffff;
        }

        .table-responsive {
            border-radius: 0.5rem;
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--mc-border);
            padding: 0.85rem 1rem;
        }

        .table-custom tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            color: #1e293b;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Footer */
        .footer-custom {
            padding: 1rem 1.75rem;
            background: #ffffff;
            border-top: 1px solid var(--mc-border);
            font-size: 0.82rem;
            color: #64748b;
        }

        /* Mobile responsiveness */
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <div class="brand-text">
                <div class="fw-bold fs-5 text-white">MotoCare</div>
                <div class="text-secondary" style="font-size: 0.7rem;">Workshop Management</div>
            </div>
        </div>

        <ul class="nav flex-column nav-sidebar">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Operasional Bengkel -->
            <li class="sidebar-heading">Operasional Bengkel</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('receptions.*') ? 'active' : '' }}" href="{{ route('receptions.index') }}">
                    <i class="bi bi-box-arrow-in-down-right"></i>
                    <span>Penerimaan Kendaraan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}" href="{{ route('work-orders.index') }}">
                    <i class="bi bi-clipboard2-pulse"></i>
                    <span>Work Order (SPK)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Pelanggan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                    <i class="bi bi-scooter"></i>
                    <span>Kendaraan</span>
                </a>
            </li>

            <!-- Master Data -->
            <li class="sidebar-heading">Master Data</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('mechanics.*') ? 'active' : '' }}" href="{{ route('mechanics.index') }}">
                    <i class="bi bi-wrench-adjustable"></i>
                    <span>Data Mekanik</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                    <i class="bi bi-card-checklist"></i>
                    <span>Jasa Servis</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('spare-parts.index') || request()->routeIs('spare-parts.show') || request()->routeIs('spare-parts.create') || request()->routeIs('spare-parts.edit') ? 'active' : '' }}" href="{{ route('spare-parts.index') }}">
                    <i class="bi bi-tools"></i>
                    <span>Spare Part</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('spare-parts.categories') ? 'active' : '' }}" href="{{ route('spare-parts.categories') }}">
                    <i class="bi bi-tags"></i>
                    <span>Kategori Part</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <i class="bi bi-truck"></i>
                    <span>Supplier</span>
                </a>
            </li>

            <!-- Inventaris & Pembelian -->
            <li class="sidebar-heading">Inventaris & Pembelian</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('spare-parts.movements') ? 'active' : '' }}" href="{{ route('spare-parts.movements') }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Pergerakan Stok</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                    <i class="bi bi-cart-check"></i>
                    <span>Pesanan Pembelian (PO)</span>
                </a>
            </li>

            <!-- Kasir & Keuangan -->
            <li class="sidebar-heading">Kasir & Keuangan</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="bi bi-receipt"></i>
                    <span>Invoice</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payments.index') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payments.receivables') ? 'active' : '' }}" href="{{ route('payments.receivables') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Piutang Pelanggan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Pengeluaran Bengkel</span>
                </a>
            </li>

            <!-- Laporan -->
            <li class="sidebar-heading">Laporan Bisnis</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Pusat Laporan</span>
                </a>
            </li>

            <!-- Sistem (Owner) -->
            @if(auth()->user()->isOwner())
                <li class="sidebar-heading">Sistem & Keamanan</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-person-gear"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Audit Log Aktivitas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <i class="bi bi-sliders"></i>
                        <span>Pengaturan Bengkel</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Content Wrapper -->
    <div id="content-wrapper">
        <!-- Topbar -->
        <header id="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-lg-none" type="button" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="bi bi-shop me-1"></i> {{ \App\Models\WorkshopSetting::get('workshop_name', 'MotoCare Workshop') }}
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 36px; height: 36px;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="d-none d-md-block text-start me-1" style="line-height: 1.2;">
                            <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ auth()->user()->role_names }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ auth()->user()->name }}</div>
                            <div class="text-muted small">{{ auth()->user()->email }}</div>
                            <span class="badge bg-secondary mt-1">{{ auth()->user()->role_names }}</span>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> Profil & Password</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="main-container">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terdapat kesalahan pengisian data:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer-custom d-flex flex-wrap justify-content-between align-items-center">
            <div>&copy; {{ date('Y') }} <strong>MotoCare Workshop Management System</strong>. Hak cipta dilindungi.</div>
            <div class="text-muted">Zona Waktu: <strong>Asia/Jakarta (WIB)</strong> &bull; Versi 1.0.0</div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>

    @stack('scripts')
</body>
</html>


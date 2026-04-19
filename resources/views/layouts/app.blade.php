<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Inventory Barang (Laravel)' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navbar-font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --admin-nav-gradient: linear-gradient(118deg, #0a2349 0%, #0b5ed7 50%, #1f7ee5 100%);
            --admin-nav-surface: rgba(7, 20, 46, 0.34);
            --admin-nav-border: rgba(255, 255, 255, 0.2);
            --admin-nav-text: #f8fbff;
            --admin-nav-active-text: #0f2858;
            --admin-nav-active-bg: linear-gradient(135deg, #ffffff 0%, #eaf2ff 100%);
        }

        body { background: #f5f7fb; }
        .has-fixed-nav { padding-top: 72px; }
        .navbar-brand { font-weight: 400; }
        .navbar { z-index: 1080; }
        .card { border: 0; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06); }
        .table thead th { white-space: nowrap; }
        .table > :not(caption) > * > :first-child { padding-left: 1rem; }

        #borrowFaceResult,
        #returnFaceResult,
        #faceRegisterAlert,
        #publicRegisterFaceAlert {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 0.95rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10);
            padding: 0.95rem 1rem;
            font-weight: 700;
            line-height: 1.45;
            letter-spacing: 0.01em;
            text-wrap: balance;
        }

        #borrowFaceResult::before,
        #returnFaceResult::before,
        #faceRegisterAlert::before,
        #publicRegisterFaceAlert::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.18), transparent 42%, rgba(255, 255, 255, 0.08));
            pointer-events: none;
        }

        #borrowFaceResult.alert-secondary,
        #returnFaceResult.alert-secondary,
        #faceRegisterAlert.alert-secondary,
        #publicRegisterFaceAlert.alert-secondary {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #0f172a;
            border-left: 0.35rem solid #64748b;
        }

        #borrowFaceResult.alert-info,
        #returnFaceResult.alert-info,
        #faceRegisterAlert.alert-info,
        #publicRegisterFaceAlert.alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1e3a8a;
            border-left: 0.35rem solid #3b82f6;
        }

        #borrowFaceResult.alert-success,
        #returnFaceResult.alert-success,
        #faceRegisterAlert.alert-success,
        #publicRegisterFaceAlert.alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
            border-left: 0.35rem solid #10b981;
        }

        #borrowFaceResult.alert-warning,
        #returnFaceResult.alert-warning,
        #faceRegisterAlert.alert-warning,
        #publicRegisterFaceAlert.alert-warning {
            background: linear-gradient(135deg, #fff7ed 0%, #fde68a 100%);
            color: #92400e;
            border-left: 0.35rem solid #f59e0b;
        }

        #borrowFaceResult.alert-danger,
        #returnFaceResult.alert-danger,
        #faceRegisterAlert.alert-danger,
        #publicRegisterFaceAlert.alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 0.35rem solid #ef4444;
        }

        #borrowRecognizedUser,
        #returnRecognizedUser {
            display: block;
            min-height: 2.8rem;
            padding: 0.7rem 0.85rem;
            border-radius: 0.8rem;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(15, 23, 42, 0.03));
            border: 1px dashed rgba(37, 99, 235, 0.24);
            color: #0f172a;
            line-height: 1.45;
            word-break: break-word;
        }

        .navbar-brand,
        .admin-nav-link,
        .admin-login-pill,
        .admin-logout-btn,
        .public-brand-text,
        .public-brand-subtext,
        .public-navbar .btn {
            font-family: var(--navbar-font-family);
        }

        .public-body { background: #e9edf2; }
        .public-navbar { background: #ffffff; border-bottom: 1px solid #d5dce6; }
        .public-brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #0d6efd;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .public-brand-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.24rem;
        }

        .public-brand-text {
            font-size: 1.72rem;
            line-height: 1.1;
        }

        .public-brand-subtext {
            font-size: 1.42rem;
        }

        .admin-navbar {
            position: fixed;
            overflow: hidden;
            background: var(--admin-nav-gradient);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 14px 34px rgba(7, 20, 46, 0.34);
        }

        .admin-navbar::before,
        .admin-navbar::after {
            content: '';
            position: absolute;
            pointer-events: none;
        }

        .admin-navbar::before {
            inset: 0;
            background: linear-gradient(130deg, rgba(255, 255, 255, 0.14), transparent 42%, rgba(255, 255, 255, 0.08));
            z-index: 0;
        }

        .admin-navbar::after {
            width: 260px;
            height: 260px;
            top: -150px;
            right: 8%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 70%);
            z-index: 0;
        }

        .admin-nav-shell {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .admin-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            margin-right: 0.2rem;
            color: #ffffff !important;
            text-decoration: none;
            letter-spacing: 0.01em;
        }

        .admin-brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.33);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3), 0 10px 18px rgba(5, 14, 30, 0.25);
        }

        .admin-brand-mark i {
            font-size: 0.98rem;
        }

        .admin-brand-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.24rem;
        }

        .admin-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.02;
        }

        .admin-brand-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.02em;
        }

        .admin-brand-subtitle {
            margin-top: 0.08rem;
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.50em;
            text-transform: uppercase;
            color: rgba(236, 245, 255, 0.86);
        }

        .admin-nav-center {
            align-items: center;
            gap: 0.34rem;
            padding: 0.34rem;
            border-radius: 999px;
            background: var(--admin-nav-surface);
            border: 1px solid var(--admin-nav-border);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
        }

        .admin-nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 700;
            font-size: 0.88rem;
            letter-spacing: 0.01em;
            color: var(--admin-nav-text) !important;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.5rem 0.86rem !important;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-nav-link i {
            font-size: 0.9rem;
            opacity: 0.95;
            transition: transform 0.2s ease;
        }

        .admin-nav-link:hover,
        .admin-nav-link:focus-visible {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(7, 20, 46, 0.24);
        }

        .admin-nav-link:hover i,
        .admin-nav-link:focus-visible i {
            transform: translateX(1px);
        }

        .admin-nav-link.active {
            color: var(--admin-nav-active-text) !important;
            background: var(--admin-nav-active-bg);
            border-color: rgba(255, 255, 255, 0.56);
            box-shadow: 0 8px 16px rgba(5, 14, 30, 0.2);
        }

        .admin-nav-link.active i {
            color: #0b5ed7;
        }

        .admin-login-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.28rem;
            color: rgba(255, 255, 255, 0.95);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 999px;
            padding: 0.24rem 0.68rem;
            font-size: 0.76rem;
            font-weight: 100;
            white-space: nowrap;
            box-shadow: none;
        }

        .admin-logout-btn {
            border-radius: 999px;
            font-weight: 700;
            padding-left: 0.92rem;
            padding-right: 0.92rem;
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(7, 20, 46, 0.18);
            white-space: nowrap;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .admin-logout-btn:hover,
        .admin-logout-btn:focus-visible {
            color: #0f2858;
            background: #ffffff;
            border-color: #ffffff;
        }

        .admin-navbar .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 0.85rem;
            padding: 0.36rem 0.52rem;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .admin-navbar .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(191, 219, 254, 0.42);
        }

        @media (min-width: 992px) {
            .admin-navbar .navbar-collapse {
                min-height: 60px;
            }

            .admin-nav-center {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                margin-bottom: 0 !important;
            }

            .admin-nav-right {
                margin-left: auto;
            }
        }

        @media (max-width: 991.98px) {
            .has-fixed-nav {
                padding-top: 84px;
            }

            .admin-brand {
                gap: 0.55rem;
            }

            .admin-brand-mark {
                width: 34px;
                height: 34px;
                border-radius: 0.76rem;
            }

            .admin-brand-title {
                font-size: 0.94rem;
            }

            .admin-brand-subtitle {
                font-size: 0.58rem;
            }

            .admin-navbar .navbar-collapse {
                margin-top: 0.75rem;
                padding: 0.78rem;
                border: 1px solid rgba(255, 255, 255, 0.22);
                border-radius: 1rem;
                background: rgba(7, 20, 46, 0.42);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
                backdrop-filter: blur(8px);
            }

            .admin-nav-center {
                width: 100%;
                border-radius: 0.95rem;
                gap: 0.2rem;
                padding: 0.45rem;
            }

            .admin-nav-link {
                width: 100%;
                justify-content: flex-start;
                border-radius: 0.78rem;
                padding: 0.6rem 0.72rem !important;
            }

            .admin-nav-right {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px dashed rgba(255, 255, 255, 0.34);
            }

            .admin-login-pill,
            .admin-logout-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 991.98px) {
            .public-brand-icon {
                width: 34px;
                height: 34px;
            }

            .public-brand-text {
                font-size: 1.15rem;
            }

            .public-brand-subtext {
                font-size: 0.92rem;
            }
        }

        @media (max-width: 575.98px) {
            #borrowFaceResult,
            #returnFaceResult,
            #faceRegisterAlert,
            #publicRegisterFaceAlert {
                padding: 0.82rem 0.9rem;
                font-size: 0.92rem;
            }

            #borrowRecognizedUser,
            #returnRecognizedUser {
                min-height: 2.6rem;
                padding: 0.62rem 0.75rem;
                font-size: 0.88rem;
            }
        }
    </style>
    @stack('styles')
</head>
@php
    $isPublicDashboard = request()->routeIs('dashboard.public');
    $adminSession = session('admin_access');
    $loggedInAdminName = is_array($adminSession)
        ? (string) ($adminSession['user_name'] ?? 'Administrator')
        : 'Administrator';

    $navbarSettingDefaults = [
        'public_nav_logo_path' => '',
        'public_nav_brand_text' => 'SIM-IV',
        'public_nav_brand_subtext' => 'School Inventory System',
        'admin_nav_logo_path' => '',
        'admin_nav_brand_title' => 'Inventory Barang',
        'admin_nav_brand_subtitle' => 'SMK NEGERI 1 CIOMAS',
    ];

    $storedNavbarSettings = \App\Models\Setting::query()
        ->whereIn('setting_key', array_keys($navbarSettingDefaults))
        ->pluck('setting_value', 'setting_key');

    $navbarSettings = $navbarSettingDefaults;

    foreach ($storedNavbarSettings as $key => $value) {
        if (!is_string($key) || !array_key_exists($key, $navbarSettings)) {
            continue;
        }

        if ($value !== null && trim((string) $value) !== '') {
            $navbarSettings[$key] = trim((string) $value);
        }
    }

    $buildLogoUrl = static function (string $logoPath): ?string {
        $normalizedPath = trim($logoPath);

        if ($normalizedPath === '') {
            return null;
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($normalizedPath)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($normalizedPath);
    };

    $publicNavLogoUrl = $buildLogoUrl((string) ($navbarSettings['public_nav_logo_path'] ?? ''));
    $publicNavBrandText = (string) ($navbarSettings['public_nav_brand_text'] ?? $navbarSettingDefaults['public_nav_brand_text']);
    $publicNavBrandSubtext = (string) ($navbarSettings['public_nav_brand_subtext'] ?? $navbarSettingDefaults['public_nav_brand_subtext']);
    $adminNavLogoUrl = $buildLogoUrl((string) ($navbarSettings['admin_nav_logo_path'] ?? ''));
    $adminNavBrandTitle = (string) ($navbarSettings['admin_nav_brand_title'] ?? $navbarSettingDefaults['admin_nav_brand_title']);
    $adminNavBrandSubtitle = (string) ($navbarSettings['admin_nav_brand_subtitle'] ?? $navbarSettingDefaults['admin_nav_brand_subtitle']);
@endphp
<body class="{{ $isPublicDashboard ? 'public-body has-fixed-nav' : 'has-fixed-nav' }}">
@if($isPublicDashboard)
    <nav class="navbar fixed-top js-fixed-navbar public-navbar py-2 shadow-sm">
        <div class="container-fluid px-3 px-md-4">
            <a class="navbar-brand d-flex align-items-center gap-2 mb-0" href="{{ route('dashboard.public') }}">
                <span class="public-brand-icon">
                    @if($publicNavLogoUrl)
                        <img src="{{ $publicNavLogoUrl }}" alt="Logo Public" class="public-brand-logo">
                    @else
                        <i class="fa-solid fa-boxes-stacked"></i>
                    @endif
                </span>
                <span class="text-dark fw-bold public-brand-text">
                    {{ $publicNavBrandText }}
                    <span class="text-secondary fw-semibold public-brand-subtext">| {{ $publicNavBrandSubtext }}</span>
                </span>
            </a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="btn btn-light border rounded-pill px-3 text-secondary" disabled>
                    <i class="fa-solid fa-globe me-2"></i>Public Mode
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#adminLoginModal">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Login Admin
                </button>
            </div>
        </div>
    </nav>

    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminLoginModalLabel">Login Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="modal-body">
                        <label for="adminPassword" class="form-label">Masukkan password admin</label>
                        <input
                            type="password"
                            id="adminPassword"
                            name="password"
                            class="form-control form-control-lg"
                            placeholder="Password admin"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-lock-open me-2"></i>Masuk Dashboard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <main class="container-fluid px-3 px-md-4 py-3 py-md-4">
        @include('partials.flash')
        @yield('content')
    </main>
@else
    <nav class="navbar fixed-top js-fixed-navbar navbar-expand-lg navbar-dark bg-primary shadow-sm admin-navbar">
        <div class="container-fluid px-4 admin-nav-shell">
            <a class="navbar-brand admin-brand" href="{{ route('dashboard.public') }}">
                <span class="admin-brand-mark">
                    @if($adminNavLogoUrl)
                        <img src="{{ $adminNavLogoUrl }}" alt="Logo Admin" class="admin-brand-logo">
                    @else
                        <i class="fa-solid fa-boxes-stacked"></i>
                    @endif
                </span>
                <span class="admin-brand-text">
                    <span class="admin-brand-title">{{ $adminNavBrandTitle }}</span>
                    <span class="admin-brand-subtitle">{{ $adminNavBrandSubtitle }}</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav admin-nav-center mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}" href="{{ route('dashboard.admin') }}">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Admin Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}" href="{{ route('admin.assets.index') }}">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <span>Data Barang</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Data Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.loans.*') ? 'active' : '' }}" href="{{ route('admin.loans.index') }}">
                            <i class="fa-solid fa-barcode"></i>
                            <span>Barcode</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.face-register.*') ? 'active' : '' }}" href="{{ route('admin.face-register.index') }}">
                            <i class="fa-solid fa-face-viewfinder"></i>
                            <span>Register Wajah</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                            <i class="fa-solid fa-gears"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                </ul>

                <div class="admin-nav-right d-flex flex-column flex-lg-row align-items-lg-center gap-2 ms-lg-auto">
                    <span class="admin-login-pill">
                        <i class="fa-solid fa-user-shield"></i>
                        : {{ $loggedInAdminName }}
                    </span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm admin-logout-btn">
                            <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-4 py-3">
        @include('partials.flash')
        @yield('content')
    </main>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fixedNavbar = document.querySelector('.js-fixed-navbar');

        if (!fixedNavbar) {
            return;
        }

        var syncNavbarOffset = function () {
            document.body.style.paddingTop = fixedNavbar.offsetHeight + 'px';
        };

        syncNavbarOffset();
        window.addEventListener('resize', syncNavbarOffset);

        var shouldOpenAdminLoginModal = @json($isPublicDashboard && ($errors->has('password') || session('show_admin_login')));
        if (shouldOpenAdminLoginModal) {
            var adminLoginModalElement = document.getElementById('adminLoginModal');

            if (adminLoginModalElement) {
                var adminLoginModal = new bootstrap.Modal(adminLoginModalElement);
                adminLoginModal.show();
            }
        }
    });
</script>
@stack('scripts')
</body>
</html>

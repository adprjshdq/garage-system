<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') — MotoCare Workshop Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --mc-primary: #1e3a8a;
            --mc-accent: #f97316;
            --mc-bg: #f8fafc;
        }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #312e81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            padding: 1.5rem 0;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            background: #ffffff;
            overflow: hidden;
        }
        .login-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 2rem 2rem 1.5rem 2rem;
            text-align: center;
        }
        .logo-icon {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #1e3a8a, #f97316);
            color: #ffffff;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.3);
        }
        .btn-primary-mc {
            background-color: var(--mc-primary);
            border-color: var(--mc-primary);
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-primary-mc:hover {
            background-color: #172554;
            border-color: #172554;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .badge-role {
            font-size: 0.75rem;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .badge-role:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>


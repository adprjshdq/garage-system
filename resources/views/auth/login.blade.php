@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="card login-card">
    <div class="login-header">
        <div class="logo-icon">
            <i class="bi bi-gear-wide-connected"></i>
        </div>
        <h4 class="fw-bold mb-1" style="color: var(--mc-primary);">MotoCare</h4>
        <p class="text-muted small mb-0">Workshop Management System</p>
    </div>

    <div class="card-body p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                {{ $errors->first() }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="loginInput" class="form-label small fw-semibold text-secondary">Email atau Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="login" id="loginInput" class="form-control border-start-0 ps-0 @error('login') is-invalid @enderror" value="{{ old('login', 'owner@motocare.com') }}" placeholder="contoh: owner@motocare.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="passwordInput" class="form-label small fw-semibold text-secondary mb-0">Kata Sandi</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" value="password123" placeholder="Masukkan kata sandi" required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword"><i class="bi bi-eye"></i></button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="rememberMe" checked>
                <label class="form-check-label small text-muted" for="rememberMe">Ingat saya di perangkat ini</label>
            </div>

            <button type="submit" class="btn btn-primary-mc w-100 mb-3 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
            </button>
        </form>

        <div class="border-top pt-3 mt-3">
            <p class="small text-muted fw-semibold mb-2 text-center"><i class="bi bi-person-badge me-1"></i> Akun Demo Cepat (Klik untuk isi):</p>
            <div class="d-flex flex-wrap gap-1 justify-content-center">
                <span class="badge bg-primary badge-role" onclick="setDemo('owner@motocare.com')">Owner</span>
                <span class="badge bg-success badge-role" onclick="setDemo('kasir@motocare.com')">Admin/Kasir</span>
                <span class="badge bg-info text-dark badge-role" onclick="setDemo('sa@motocare.com')">Service Advisor</span>
                <span class="badge bg-warning text-dark badge-role" onclick="setDemo('mekanik@motocare.com')">Mekanik</span>
                <span class="badge bg-secondary badge-role" onclick="setDemo('gudang@motocare.com')">Gudang</span>
            </div>
            <p class="text-center text-muted" style="font-size: 0.7rem; margin-top: 6px;">Password default: <code>password123</code></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setDemo(email) {
        document.getElementById('loginInput').value = email;
        document.getElementById('passwordInput').value = 'password123';
    }

    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const input = document.getElementById('passwordInput');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>
@endpush


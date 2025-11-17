<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PStore Absensi</title>
    
    <!-- PERBAIKAN: Gunakan path yang sesuai dengan struktur folder -->
    <link rel="stylesheet" href="/assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="/assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="shortcut icon" href="/assets/images/favicon.png" />

    <style>
        /* Reset dan base styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .auth-page {
            background: #f8f9fa;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .auth-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(0, 0, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 0, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 0, 0, 0.03) 0%, transparent 50%);
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.08);
            margin: 2rem auto;
            max-width: 400px;
            width: 90%;
        }

        .login-card:hover {
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
            padding-top: 1rem;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: #000;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .logo-container i {
            font-size: 2rem;
            color: white;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
            font-size: 14px;
            width: 100%;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
            background: white;
            outline: none;
        }

        .btn-login {
            background: #000;
            border: 2px solid #000;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: white;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-login:hover {
            background: #333;
            border-color: #333;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .auth-link {
            color: #000;
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .auth-link:hover {
            color: #333;
            text-decoration: underline;
        }

        .form-check-input:checked {
            background-color: #000;
            border-color: #000;
        }

        .form-check-label {
            font-size: 14px;
            color: #64748b;
        }

        .input-group {
            display: flex;
            margin-bottom: 1rem;
        }

        .input-group-text {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            z-index: 10;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-size: 14px;
            padding: 12px 16px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .text-muted {
            color: #64748b !important;
        }

        .text-primary {
            color: #000 !important;
        }

        .border-top {
            border-top: 2px solid #e2e8f0 !important;
            padding-top: 1rem;
        }

        /* Geometric pattern */
        .geometric-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.03;
            background-image:
                linear-gradient(30deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(150deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(30deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(150deg, #000 12%, transparent 12.5%, transparent 87%, #000 87.5%, #000),
                linear-gradient(60deg, transparent 74%, #000 75%, #000 75%, transparent 76%),
                linear-gradient(60deg, transparent 74%, #000 75%, #000 75%, transparent 76%);
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-card {
                margin: 1rem;
                padding: 2rem 1.5rem !important;
            }

            .logo-container {
                width: 60px;
                height: 60px;
            }

            .logo-container i {
                font-size: 1.5rem;
            }
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }

        .mb-1 { margin-bottom: 0.25rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-4 { margin-top: 1.5rem; }
        .pt-3 { padding-top: 1rem; }
        .py-5 { padding-top: 3rem; padding-bottom: 3rem; }
        .px-4 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .px-sm-5 { padding-left: 2rem; padding-right: 2rem; }
        .me-2 { margin-right: 0.5rem; }

        .fw-bold {
            font-weight: bold;
        }

        .small {
            font-size: 0.875rem;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            float: right;
        }
    </style>
</head>

<body>
    <div class="auth-page">
        <div class="geometric-pattern"></div>

        <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
            <div class="login-card">
                <div class="brand-logo">
                    <div class="logo-container">
                        <i>🔒</i>
                    </div>
                    <h3 class="text-primary mb-1 fw-bold">PSTORE</h3>
                    <p class="text-muted">Sistem Absensi Digital</p>
                </div>

                <h4 style="text-align: center; margin-bottom: 1rem; color: #000;">Selamat Datang</h4>
                <p style="text-align: center; color: #64748b; margin-bottom: 2rem;">Masuk untuk mengakses dashboard</p>

                {{-- Tampilkan Error Jika Gagal Login --}}
                @error('email')
                    <div class="alert alert-danger">
                        <span>⚠️</span>
                        {{ $message }}
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                @enderror

                @if(session('status'))
                    <div class="alert alert-success">
                        <span>✅</span>
                        {{ session('status') }}
                        <button type="button" class="btn-close">&times;</button>
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" style="padding: 0 1rem;">
                    @csrf
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-text">
                                📧
                            </span>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Alamat Email" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="password-container">
                            <div class="input-group">
                                <span class="input-group-text">
                                    🔒
                                </span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <span id="password-icon">👁️</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <button type="submit" class="btn-login">
                            🚪 MASUK SISTEM
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 1.5rem;">
                        <div>
                            <label style="font-size: 14px; color: #64748b;">
                                <input type="checkbox" name="remember" style="margin-right: 0.5rem;">
                                Ingat saya
                            </label>
                        </div>
                        <a href="#" class="auth-link">
                            Lupa password?
                        </a>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small">
                            &copy; 2024 PStore Absensi System. All rights reserved.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                passwordIcon.textContent = '👁️';
            }
        }

        // Add loading state to form
        document.querySelector('form').addEventListener('submit', function (e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '⏳ Memproses...';
            btn.disabled = true;
        });

        // Close alert buttons
        document.querySelectorAll('.btn-close').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>

</html>
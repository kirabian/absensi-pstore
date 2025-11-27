<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Password Baru - PStore Absensi</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <style>
        /* CSS Konsisten */
        .auth-page { background: #f8f9fa; min-height: 100vh; position: relative; overflow: hidden; }
        .login-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid rgba(0,0,0,0.08); }
        .logo-container { width: 60px; height: 60px; background: #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .logo-container i { font-size: 1.5rem; color: white; }
        .form-control { border-radius: 10px; padding: 12px 16px; background: #f8fafc; border: 2px solid #e2e8f0; }
        .form-control:focus { border-color: #000; background: white; }
        .btn-login { background: #000; border: 2px solid #000; border-radius: 10px; padding: 14px; color: white; font-weight: 600; transition: 0.3s; }
        .btn-login:hover { background: #333; border-color: #333; }
    </style>
</head>
<body>
    <div class="container-scroller auth-page">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth px-0">
                <div class="row w-100 mx-0 justify-content-center">
                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-8">
                        <div class="auth-form-light text-center py-5 px-4 px-sm-5 login-card">
                            <div class="brand-logo">
                                <div class="logo-container">
                                    <i class="mdi mdi-key-variant"></i>
                                </div>
                                <h4 class="fw-bold text-dark">Buat Password Baru</h4>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger text-start">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="pt-3" action="{{ route('password.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group mb-3">
                                    <input type="email" class="form-control" name="email" placeholder="Email" value="{{ $email ?? old('email') }}" readonly>
                                </div>

                                <div class="form-group mb-3">
                                    <input type="password" class="form-control" name="password" placeholder="Password Baru (Min 8 Karakter)" required autofocus>
                                </div>

                                <div class="form-group mb-4">
                                    <input type="password" class="form-control" name="password_confirmation" placeholder="Konfirmasi Password Baru" required>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-login w-100">
                                        RESET PASSWORD
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
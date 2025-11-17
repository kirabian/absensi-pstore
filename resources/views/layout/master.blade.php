<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title')</title>
    
    <!-- PERBAIKAN: Gunakan path absolut -->
    <link rel="stylesheet" href="/assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="/assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="/assets/js/select.dataTables.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="shortcut icon" href="/assets/images/favicon.png" />

    <!-- Fallback CSS jika assets tidak load -->
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        
        .container-scroller {
            min-height: 100vh;
        }
        
        /* Loading state untuk halaman */
        .page-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-size: 18px;
            color: #666;
        }
    </style>

    @stack('styles')
</head>

<body class="with-welcome-text">
    <!-- Loading fallback -->
    <div id="pageLoading" class="page-loading" style="display: none;">
        Memuat halaman...
    </div>

    <div class="container-scroller">
        @include('layout.header')
        <div class="container-fluid page-body-wrapper">
            @include('layout.sidebar')
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                @include('layout.footer')
            </div>
        </div>
    </div>

    <!-- PERBAIKAN: Gunakan path absolut untuk JavaScript -->
    <script src="/assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/vendors/chart.js/chart.umd.js"></script>
    <script src="/assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="/assets/js/off-canvas.js"></script>
    <script src="/assets/js/template.js"></script>
    <script src="/assets/js/settings.js"></script>
    <script src="/assets/js/hoverable-collapse.js"></script>
    <script src="/assets/js/todolist.js"></script>
    <script src="/assets/js/jquery.cookie.js"></script>
    <script src="/assets/js/dashboard.js"></script>

    <!-- Fallback JavaScript jika assets tidak load -->
    <script>
        // Cek jika assets gagal load
        document.addEventListener('DOMContentLoaded', function() {
            // Cek jika jQuery terload (biasanya dari vendor.bundle.base.js)
            if (typeof jQuery === 'undefined') {
                console.warn('jQuery tidak terload, menggunakan fallback');
                // Load jQuery dari CDN sebagai fallback
                var script = document.createElement('script');
                script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                document.head.appendChild(script);
            }
            
            // Sembunyikan loading indicator setelah 3 detik
            setTimeout(function() {
                var loadingEl = document.getElementById('pageLoading');
                if (loadingEl) {
                    loadingEl.style.display = 'none';
                }
            }, 3000);
        });

        // Error handling untuk assets yang gagal load
        window.addEventListener('error', function(e) {
            console.warn('Asset gagal load:', e.target.src || e.target.href);
        }, true);
    </script>

    {{-- Selalu letakkan @stack('scripts') di PALING AKHIR --}}
    @stack('scripts')
</body>

</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin-top: 3cm;
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
            background-color: #ffffff;
        }

        /* Header Style */
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
            background-color: #2c3e50; /* Dark Blue Header */
            color: white;
            padding: 0 2cm;
            line-height: 2.5cm;
        }
        header .logo {
            float: left;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        header .doc-id {
            float: right;
            font-size: 10px;
            opacity: 0.8;
        }

        /* Title Section */
        .title-section {
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }
        .title-section h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
            text-transform: uppercase;
        }
        .title-section .subtitle {
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 5px;
        }

        /* User Info Grid */
        .info-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-box {
            width: 48%;
            float: left;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        .info-box.right {
            float: right;
            border-left-color: #2ecc71;
            margin-left: 2%; /* Gap */
        }
        .info-row {
            margin-bottom: 8px;
            clear: both;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 80px;
            display: inline-block;
            text-transform: uppercase;
            font-size: 10px;
        }
        .info-value {
            color: #333;
            font-weight: 600;
        }

        /* Statistics Cards */
        .stats-wrapper {
            width: 100%;
            margin-top: 20px;
        }
        .stat-card {
            width: 30%; /* 3 cards per row approx */
            float: left;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-right: 3%;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .stat-card:nth-child(3n) {
            margin-right: 0;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #7f8c8d;
            letter-spacing: 0.5px;
        }

        /* Progress Bar */
        .progress-bg {
            height: 6px;
            width: 100%;
            background-color: #ecf0f1;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 3px;
        }
        
        /* Colors */
        .text-success { color: #27ae60; }
        .bg-success { background-color: #27ae60; }
        
        .text-warning { color: #f39c12; }
        .bg-warning { background-color: #f39c12; }
        
        .text-danger { color: #c0392b; }
        .bg-danger { background-color: #c0392b; }
        
        .text-info { color: #2980b9; }
        .bg-info { background-color: #2980b9; }
        
        .text-grey { color: #7f8c8d; }
        .bg-grey { background-color: #7f8c8d; }

        /* Footer */
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            background-color: #f8f9fa;
            color: #7f8c8d;
            text-align: center;
            line-height: 1.5cm;
            font-size: 10px;
            border-top: 1px solid #e0e0e0;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">SISTEM ABSENSI</div>
        <div class="doc-id">GEN-{{ date('dmY-His') }}</div>
    </header>

    <footer>
        Dicetak otomatis pada {{ $export_date }} | Halaman <span class="page-number">1</span>
    </footer>

    <div class="main-content">
        
        <div class="title-section">
            <h1>{{ $title }}</h1>
            <div class="subtitle">Laporan resmi aktivitas absensi dan kehadiran.</div>
        </div>

        <div class="info-container clearfix">
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">NAMA</span>
                    <span class="info-value">{{ strtoupper($user->name) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">EMAIL</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
            </div>
            <div class="info-box right">
                <div class="info-row">
                    <span class="info-label">JABATAN</span>
                    <span class="info-value">{{ strtoupper(str_replace('_', ' ', $role)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">PERIODE</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($period)->format('d F Y') }}</span>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #2c3e50;">
            RINGKASAN STATISTIK
        </div>

        <div class="stats-wrapper clearfix">
            
            @php 
                // Alias agar penulisan lebih pendek dan aman
                // Default value array kosong jika undefined
                $s = $stats ?? []; 
            @endphp

            {{-- ======================== ADMIN ======================== --}}
            @if($role == 'Admin')
                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['present'] ?? 0 }}</div>
                    <div class="stat-label">Total Hadir</div>
                    <div class="progress-bg"><div class="progress-fill bg-success" style="width: {{ $s['present_percentage'] ?? 0 }}%"></div></div>
                    <div style="font-size:10px; margin-top:5px; color:#aaa">{{ $s['present_percentage'] ?? 0 }}% dari Total User</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-warning">{{ $s['late'] ?? 0 }}</div>
                    <div class="stat-label">Total Terlambat</div>
                    <div class="progress-bg"><div class="progress-fill bg-warning" style="width: {{ $s['late_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-danger">{{ $s['absent'] ?? 0 }}</div>
                    <div class="stat-label">Tidak Hadir</div>
                    <div class="progress-bg"><div class="progress-fill bg-danger" style="width: {{ $s['absent_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="clearfix"></div> <div class="stat-card" style="margin-top: 15px;">
                    <div class="stat-number text-info">{{ $s['pending'] ?? 0 }}</div>
                    <div class="stat-label">Menunggu Verifikasi</div>
                    <div class="progress-bg"><div class="progress-fill bg-info" style="width: {{ $s['pending_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="stat-card" style="margin-top: 15px;">
                    <div class="stat-number text-success">{{ $s['on_time'] ?? 0 }}</div>
                    <div class="stat-label">Tepat Waktu</div>
                </div>

            {{-- ======================== AUDIT ======================== --}}
            @elseif($role == 'Audit')
                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['verified'] ?? 0 }}</div>
                    <div class="stat-label">Terverifikasi</div>
                    <div class="progress-bg"><div class="progress-fill bg-success" style="width: {{ $s['verified_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-warning">{{ $s['pending'] ?? 0 }}</div>
                    <div class="stat-label">Menunggu</div>
                    <div class="progress-bg"><div class="progress-fill bg-warning" style="width: {{ $s['pending_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-danger">{{ $s['late'] ?? 0 }}</div>
                    <div class="stat-label">Flag Terlambat</div>
                    <div class="progress-bg"><div class="progress-fill bg-danger" style="width: {{ $s['late_percentage'] ?? 0 }}%"></div></div>
                </div>

            {{-- ======================== SECURITY ======================== --}}
            @elseif($role == 'Security')
                <div class="stat-card" style="width: 100%; margin-bottom: 20px; background-color: #f0f8ff; border: 1px solid #b6d4fe;">
                    <div class="stat-number text-info" style="text-align: center; font-size: 36px;">{{ $s['total_scans'] ?? 0 }}</div>
                    <div class="stat-label" style="text-align: center;">TOTAL AKTIVITAS SCAN HARI INI</div>
                </div>

                <div class="stat-card" style="width: 48%;">
                    <div class="stat-number text-success">{{ $s['check_in_scans'] ?? 0 }}</div>
                    <div class="stat-label">Scan Masuk</div>
                    <div class="progress-bg"><div class="progress-fill bg-success" style="width: {{ $s['check_in_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="stat-card" style="width: 48%; margin-right: 0;">
                    <div class="stat-number text-info">{{ $s['check_out_scans'] ?? 0 }}</div>
                    <div class="stat-label">Scan Pulang</div>
                    <div class="progress-bg"><div class="progress-fill bg-info" style="width: {{ $s['check_out_percentage'] ?? 0 }}%"></div></div>
                </div>

            {{-- ======================== KARYAWAN / USER BIASA ======================== --}}
            @else
                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['present'] ?? 0 }}</div>
                    <div class="stat-label">Hadir (Bulan Ini)</div>
                    <div class="progress-bg"><div class="progress-fill bg-success" style="width: 100%"></div></div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['on_time'] ?? 0 }}</div>
                    <div class="stat-label">Tepat Waktu</div>
                    <div class="progress-bg"><div class="progress-fill bg-success" style="width: {{ $s['on_time_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-warning">{{ $s['late'] ?? 0 }}</div>
                    <div class="stat-label">Terlambat</div>
                    <div class="progress-bg"><div class="progress-fill bg-warning" style="width: {{ $s['late_percentage'] ?? 0 }}%"></div></div>
                </div>

                <div class="clearfix"></div>

                <div class="stat-card" style="margin-top: 15px;">
                    <div class="stat-number text-danger">{{ $s['early'] ?? 0 }}</div>
                    <div class="stat-label">Pulang Cepat</div>
                </div>

                <div class="stat-card" style="margin-top: 15px;">
                    <div class="stat-number text-grey">{{ $s['pending'] ?? 0 }}</div>
                    <div class="stat-label">Status Pending</div>
                    <div class="progress-bg"><div class="progress-fill bg-grey" style="width: {{ $s['pending_percentage'] ?? 0 }}%"></div></div>
                </div>
            @endif

        </div>

        <div style="margin-top: 40px; padding: 15px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 4px; font-size: 11px; color: #856404;">
            <strong>Catatan:</strong>
            <p style="margin: 5px 0 0 0;">Laporan ini digenerate secara real-time berdasarkan data yang tersedia di sistem saat ini. Harap verifikasi ulang jika terdapat perbedaan data manual.</p>
        </div>

    </div>
</body>
</html>
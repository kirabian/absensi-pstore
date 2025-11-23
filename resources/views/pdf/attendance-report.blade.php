<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            color: #2d3748;
            margin-top: 3.5cm;
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2.5cm;
            background-color: #ffffff;
            line-height: 1.6;
        }

        /* Header Style - Modern & Clean */
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3cm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0 2cm;
            display: table;
            width: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        header .header-content {
            display: table-cell;
            vertical-align: middle;
        }
        
        header .logo {
            float: left;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        header .logo-subtitle {
            float: left;
            clear: left;
            font-size: 9px;
            opacity: 0.9;
            margin-top: 3px;
            font-weight: 300;
            letter-spacing: 1px;
        }
        
        header .doc-id {
            float: right;
            font-size: 10px;
            opacity: 0.95;
            text-align: right;
            background: rgba(255,255,255,0.2);
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 10px;
        }

        /* Title Section - Enhanced */
        .title-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #e2e8f0;
            position: relative;
        }
        
        .title-section::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .title-section h1 {
            color: #1a202c;
            font-size: 26px;
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .title-section .subtitle {
            color: #718096;
            font-size: 12px;
            font-weight: 400;
        }

        /* User Info Grid - Improved Layout */
        .info-container {
            width: 100%;
            margin-bottom: 30px;
            display: table;
            table-layout: fixed;
        }
        
        .info-box {
            width: 48%;
            float: left;
            padding: 18px 20px;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-left: 4px solid #667eea;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .info-box.right {
            float: right;
            border-left-color: #48bb78;
        }
        
        .info-row {
            margin-bottom: 12px;
            clear: both;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 700;
            color: #4a5568;
            width: 90px;
            display: inline-block;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #1a202c;
            font-weight: 600;
            font-size: 12px;
        }

        /* Section Header */
        .section-header {
            margin: 30px 0 20px 0;
            font-weight: 700;
            font-size: 15px;
            color: #1a202c;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-left: 12px;
            border-left: 4px solid #667eea;
        }

        /* Statistics Cards - Professional Design */
        .stats-wrapper {
            width: 100%;
            margin-top: 20px;
        }
        
        .stat-card {
            width: 31%;
            float: left;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            margin-right: 3.5%;
            margin-bottom: 20px;
            box-sizing: border-box;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        
        .stat-card:nth-child(3n) {
            margin-right: 0;
        }
        
        .stat-card.full-width {
            width: 100%;
            margin-right: 0;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #bae6fd;
            padding: 22px;
        }
        
        .stat-card.half-width {
            width: 48%;
            margin-right: 4%;
        }
        
        .stat-card.half-width:nth-child(2n) {
            margin-right: 0;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 6px;
            line-height: 1;
        }
        
        .stat-card.full-width .stat-number {
            font-size: 42px;
            text-align: center;
        }
        
        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #718096;
            letter-spacing: 0.8px;
            font-weight: 600;
        }
        
        .stat-card.full-width .stat-label {
            text-align: center;
            font-size: 11px;
        }
        
        .stat-detail {
            font-size: 9px;
            margin-top: 6px;
            color: #a0aec0;
            font-style: italic;
        }

        /* Progress Bar - Enhanced */
        .progress-bg {
            height: 8px;
            width: 100%;
            background-color: #e2e8f0;
            border-radius: 4px;
            margin-top: 12px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        /* Colors - Updated Palette */
        .text-success { color: #48bb78; }
        .bg-success { background: linear-gradient(90deg, #48bb78 0%, #38a169 100%); }
        
        .text-warning { color: #ed8936; }
        .bg-warning { background: linear-gradient(90deg, #ed8936 0%, #dd6b20 100%); }
        
        .text-danger { color: #f56565; }
        .bg-danger { background: linear-gradient(90deg, #f56565 0%, #e53e3e 100%); }
        
        .text-info { color: #4299e1; }
        .bg-info { background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%); }
        
        .text-grey { color: #a0aec0; }
        .bg-grey { background: linear-gradient(90deg, #a0aec0 0%, #718096 100%); }

        /* Footer - Modern */
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: #f7fafc;
            color: #718096;
            text-align: center;
            padding-top: 0.6cm;
            font-size: 9px;
            border-top: 2px solid #e2e8f0;
        }
        
        footer .footer-text {
            margin-bottom: 4px;
        }
        
        footer .page-info {
            font-weight: 600;
            color: #4a5568;
        }

        /* Notice Box - Enhanced */
        .notice-box {
            margin-top: 35px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 2px solid #fbbf24;
            border-left: 5px solid #f59e0b;
            border-radius: 6px;
            font-size: 10px;
            color: #78350f;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.1);
        }
        
        .notice-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #92400e;
        }
        
        .notice-box p {
            margin: 0;
            line-height: 1.6;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        /* Print Optimization */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">SISTEM ABSENSI</div>
            <div class="logo-subtitle">Attendance Management System</div>
            <div class="doc-id">
                DOC ID: GEN-{{ date('dmY-His') }}<br>
                <span style="font-size: 8px;">Generated Report</span>
            </div>
        </div>
    </header>

    <footer>
        <div class="footer-text">Dicetak otomatis pada {{ $export_date }}</div>
        <div class="page-info">Halaman <span class="page-number">1</span> | Confidential Document</div>
    </footer>

    <div class="main-content">
        
        <div class="title-section">
            <h1>{{ $title }}</h1>
            <div class="subtitle">Laporan resmi aktivitas absensi dan kehadiran karyawan</div>
        </div>

        <div class="info-container clearfix">
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ strtoupper($user->name) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
            </div>
            <div class="info-box right">
                <div class="info-row">
                    <span class="info-label">Jabatan</span>
                    <span class="info-value">{{ strtoupper(str_replace('_', ' ', $role)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Periode</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($period)->format('d F Y') }}</span>
                </div>
            </div>
        </div>

        <div class="section-header">Ringkasan Statistik</div>

        <div class="stats-wrapper clearfix">
            
            @php 
                $s = $stats ?? []; 
            @endphp

            {{-- ======================== ADMIN ======================== --}}
            @if($role == 'Admin')
                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['present'] ?? 0 }}</div>
                    <div class="stat-label">Total Hadir</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-success" style="width: {{ $s['present_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['present_percentage'] ?? 0 }}% dari total user</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-warning">{{ $s['late'] ?? 0 }}</div>
                    <div class="stat-label">Total Terlambat</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-warning" style="width: {{ $s['late_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['late_percentage'] ?? 0 }}% keterlambatan</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-danger">{{ $s['absent'] ?? 0 }}</div>
                    <div class="stat-label">Tidak Hadir</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-danger" style="width: {{ $s['absent_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['absent_percentage'] ?? 0 }}% absensi</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-info">{{ $s['pending'] ?? 0 }}</div>
                    <div class="stat-label">Menunggu Verifikasi</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-info" style="width: {{ $s['pending_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">Perlu ditindaklanjuti</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['on_time'] ?? 0 }}</div>
                    <div class="stat-label">Tepat Waktu</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-success" style="width: 100%"></div>
                    </div>
                    <div class="stat-detail">Kehadiran tepat waktu</div>
                </div>

            {{-- ======================== AUDIT ======================== --}}
            @elseif($role == 'Audit')
                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['verified'] ?? 0 }}</div>
                    <div class="stat-label">Terverifikasi</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-success" style="width: {{ $s['verified_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['verified_percentage'] ?? 0 }}% telah diverifikasi</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-warning">{{ $s['pending'] ?? 0 }}</div>
                    <div class="stat-label">Menunggu Review</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-warning" style="width: {{ $s['pending_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['pending_percentage'] ?? 0 }}% dalam antrian</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-danger">{{ $s['late'] ?? 0 }}</div>
                    <div class="stat-label">Flag Terlambat</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-danger" style="width: {{ $s['late_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">Memerlukan perhatian khusus</div>
                </div>

            {{-- ======================== SECURITY ======================== --}}
            @elseif($role == 'Security')
                <div class="stat-card full-width">
                    <div class="stat-number text-info">{{ $s['total_scans'] ?? 0 }}</div>
                    <div class="stat-label">Total Aktivitas Scan Hari Ini</div>
                </div>

                <div class="stat-card half-width">
                    <div class="stat-number text-success">{{ $s['check_in_scans'] ?? 0 }}</div>
                    <div class="stat-label">Scan Masuk</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-success" style="width: {{ $s['check_in_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['check_in_percentage'] ?? 0 }}% dari total</div>
                </div>

                <div class="stat-card half-width">
                    <div class="stat-number text-info">{{ $s['check_out_scans'] ?? 0 }}</div>
                    <div class="stat-label">Scan Pulang</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-info" style="width: {{ $s['check_out_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['check_out_percentage'] ?? 0 }}% dari total</div>
                </div>

            {{-- ======================== KARYAWAN / USER BIASA ======================== --}}
            @else
                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['present'] ?? 0 }}</div>
                    <div class="stat-label">Hadir (Bulan Ini)</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-success" style="width: 100%"></div>
                    </div>
                    <div class="stat-detail">Total kehadiran bulan ini</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-success">{{ $s['on_time'] ?? 0 }}</div>
                    <div class="stat-label">Tepat Waktu</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-success" style="width: {{ $s['on_time_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['on_time_percentage'] ?? 0 }}% kehadiran tepat waktu</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-warning">{{ $s['late'] ?? 0 }}</div>
                    <div class="stat-label">Terlambat</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-warning" style="width: {{ $s['late_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['late_percentage'] ?? 0 }}% keterlambatan</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-danger">{{ $s['early'] ?? 0 }}</div>
                    <div class="stat-label">Pulang Cepat</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-danger" style="width: 50%"></div>
                    </div>
                    <div class="stat-detail">Keluar sebelum jam kerja</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number text-grey">{{ $s['pending'] ?? 0 }}</div>
                    <div class="stat-label">Status Pending</div>
                    <div class="progress-bg">
                        <div class="progress-fill bg-grey" style="width: {{ $s['pending_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="stat-detail">{{ $s['pending_percentage'] ?? 0 }}% menunggu verifikasi</div>
                </div>
            @endif

        </div>

        <div class="notice-box">
            <strong>⚠ Catatan Penting</strong>
            <p>Laporan ini digenerate secara real-time berdasarkan data yang tersedia di sistem pada saat pencetakan. Harap melakukan verifikasi ulang jika terdapat perbedaan dengan data manual. Untuk informasi lebih lanjut, hubungi departemen HR atau IT Support.</p>
        </div>

    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .info-table .label {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .stat-card .label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .stat-card .percentage {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Dibuat pada: {{ $export_date }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama User</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">Role</td>
            <td>{{ $role }}</td>
        </tr>
        <tr>
            <td class="label">Divisi</td>
            <td>{{ $user->division->name ?? 'Tidak Ada' }}</td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td>{{ $period }}</td>
        </tr>
    </table>

    <div class="stats-grid">
        @if($role == 'Admin')
            <div class="stat-card" style="border-left: 4px solid #007bff;">
                <div class="label">Hadir</div>
                <h3>{{ $present }}</h3>
                <div class="percentage" style="color: #007bff;">{{ $present_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #ffc107;">
                <div class="label">Terlambat</div>
                <h3>{{ $late }}</h3>
                <div class="percentage" style="color: #ffc107;">{{ $late_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #17a2b8;">
                <div class="label">Pending</div>
                <h3>{{ $pending }}</h3>
                <div class="percentage" style="color: #17a2b8;">{{ $pending_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #dc3545;">
                <div class="label">Tidak Hadir</div>
                <h3>{{ $absent }}</h3>
                <div class="percentage" style="color: #dc3545;">{{ $absent_percentage }}%</div>
            </div>
            
        @elseif($role == 'Audit')
            <div class="stat-card" style="border-left: 4px solid #28a745;">
                <div class="label">Terverifikasi</div>
                <h3>{{ $verified }}</h3>
                <div class="percentage" style="color: #28a745;">{{ $verified_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #ffc107;">
                <div class="label">Menunggu</div>
                <h3>{{ $pending }}</h3>
                <div class="percentage" style="color: #ffc107;">{{ $pending_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #dc3545;">
                <div class="label">Terlambat</div>
                <h3>{{ $late }}</h3>
                <div class="percentage" style="color: #dc3545;">{{ $late_percentage }}%</div>
            </div>
            
        @elseif($role == 'Security')
            <div class="stat-card" style="border-left: 4px solid #007bff;">
                <div class="label">Total Scan</div>
                <h3>{{ $total_scans }}</h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid #28a745;">
                <div class="label">Scan Masuk</div>
                <h3>{{ $check_in_scans }}</h3>
                <div class="percentage" style="color: #28a745;">{{ $check_in_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #17a2b8;">
                <div class="label">Scan Pulang</div>
                <h3>{{ $check_out_scans }}</h3>
                <div class="percentage" style="color: #17a2b8;">{{ $check_out_percentage }}%</div>
            </div>
            
        @else
            <div class="stat-card" style="border-left: 4px solid #28a745;">
                <div class="label">Hadir</div>
                <h3>{{ $present }}</h3>
                <div class="percentage" style="color: #28a745;">{{ $present_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #ffc107;">
                <div class="label">Terlambat</div>
                <h3>{{ $late }}</h3>
                <div class="percentage" style="color: #ffc107;">{{ $late_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #17a2b8;">
                <div class="label">Tepat Waktu</div>
                <h3>{{ $on_time }}</h3>
                <div class="percentage" style="color: #17a2b8;">{{ $on_time_percentage }}%</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #6c757d;">
                <div class="label">Pending</div>
                <h3>{{ $pending }}</h3>
                <div class="percentage" style="color: #6c757d;">{{ $pending_percentage }}%</div>
            </div>
        @endif
    </div>

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Sistem Absensi<br>
        © {{ date('Y') }} - All rights reserved
    </div>
</body>
</html>
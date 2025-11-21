<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; color: white; }
        .bg-green { background-color: #28a745; }
        .bg-red { background-color: #dc3545; }
        .bg-yellow { background-color: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Periode: {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Divisi</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->check_in_time->format('d/m/Y') }}</td>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->user->division->name ?? '-' }}</td>
                    <td>{{ $attendance->check_in_time->format('H:i') }}</td>
                    <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
                    <td>
                        @if($attendance->is_late_checkin)
                            <span class="badge bg-red">Telat</span>
                        @else
                            <span class="badge bg-green">Tepat Waktu</span>
                        @endif
                    </td>
                    <td>
                        @if($attendance->attendance_type == 'self')
                            Mandiri
                        @elseif($attendance->attendance_type == 'scan')
                            Scan Security
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
```

---

### 5. Update `resources/views/dashboard.blade.php`

Tambahkan **Card Grafik** dan **Tombol Export** di bagian paling bawah konten, agar tampil untuk semua role.

Copy-paste kode ini di bagian paling bawah `@section('content')` (sebelum `@endsection`):

```php
    {{-- ======================================================================= --}}
    {{--  STATISTIK & CHART (UNTUK SEMUA ROLE) --}}
    {{-- ======================================================================= --}}
    <div class="row mt-4">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">
                            <i class="mdi mdi-chart-line me-2"></i>
                            @if(auth()->user()->role == 'user_biasa')
                                Statistik Kehadiran Saya (7 Hari Terakhir)
                            @elseif(auth()->user()->role == 'security')
                                Statistik Aktivitas Scan (7 Hari Terakhir)
                            @else
                                Statistik Kehadiran Tim (7 Hari Terakhir)
                            @endif
                        </h4>
                        
                        {{-- Tombol Export PDF --}}
                        <a href="{{ route('dashboard.export-pdf') }}" class="btn btn-danger text-white btn-icon-text">
                            <i class="mdi mdi-file-pdf btn-icon-prepend"></i>
                            Export Laporan PDF
                        </a>
                    </div>
                    
                    {{-- Canvas untuk Chart.js --}}
                    <canvas id="attendanceChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT CHART.JS --}}
    {{-- Pastikan library Chart.js diload di layout master atau disini --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            // Data dari Controller
            const labels = @json($chartLabels);
            const presentData = @json($chartValues['present']);
            const lateData = @json($chartValues['late']);
            const chartType = "{{ $chartType }}"; // 'personal' atau 'team'

            // Konfigurasi Label Dataset
            const label1 = chartType === 'personal' ? 'Hadir Tepat Waktu' : 'Karyawan Tepat Waktu';
            const label2 = chartType === 'personal' ? 'Terlambat' : 'Karyawan Terlambat';

            new Chart(ctx, {
                type: 'bar', // Bisa diganti 'line'
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: label1,
                            data: presentData,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            borderRadius: 5,
                        },
                        {
                            label: label2,
                            data: lateData,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1,
                            borderRadius: 5,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 // Supaya angka bulat (orang/hari)
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        });
    </script>
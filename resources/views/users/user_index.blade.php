@extends('layout.master')

@section('title')
    Data User
@endsection

@section('heading')
    Manajemen User
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Semua User</h4>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mb-3">
                        <i class="mdi mdi-plus"></i> Tambah User Baru
                    </a>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Nama </th>
                                    <th> ID Login </th>
                                    <th> Email </th>
                                    <th> Role </th>
                                    <th> Cabang </th>
                                    <th> Divisi </th>
                                    <th> QR Code </th>
                                    <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $key => $user)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> 
                                            <div class="d-flex align-items-center">
                                                @if($user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                         alt="{{ $user->name }}" 
                                                         class="rounded-circle me-2" 
                                                         width="32" height="32">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="mdi mdi-account text-white"></i>
                                                    </div>
                                                @endif
                                                <span>{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $user->login_id }}</span>
                                        </td>
                                        <td> 
                                            @if($user->email)
                                                <span class="text-muted">{{ $user->email }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- LOGIKA BADGE ROLE --}}
                                            @if($user->role == 'admin' && $user->branch_id == null)
                                                <span class="badge badge-danger">Super Admin</span>
                                            @elseif($user->role == 'admin' && $user->branch_id != null)
                                                <span class="badge badge-primary">Admin Cabang</span>
                                            @elseif($user->role == 'audit')
                                                <span class="badge badge-info">Audit</span>
                                            @elseif($user->role == 'leader')
                                                <span class="badge badge-success">Leader</span>
                                            @elseif($user->role == 'security')
                                                <span class="badge badge-warning">Security</span>
                                            @else
                                                <span class="badge badge-secondary">Karyawan</span>
                                            @endif
                                        </td>
                                        <td> 
                                            @if($user->branch)
                                                <span class="badge badge-outline-primary">{{ $user->branch->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td> 
                                            @if($user->division)
                                                <span class="badge badge-outline-info">{{ $user->division->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->qr_code_value)
                                                <button type="button" class="btn btn-inverse-info btn-icon"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#qrModal"
                                                        data-name="{{ $user->name }}"
                                                        data-login-id="{{ $user->login_id }}"
                                                        data-qr="{{ $user->qr_code_value }}"
                                                        title="Lihat QR Code">
                                                    <i class="mdi mdi-qrcode"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('users.edit', $user->id) }}"
                                                    class="btn btn-inverse-warning btn-icon me-1"
                                                    title="Edit User">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>

                                                @if ($user->id != auth()->id())
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user {{ $user->name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-inverse-danger btn-icon"
                                                                title="Hapus User">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-inverse-secondary btn-icon" disabled
                                                            title="Tidak dapat menghapus akun sendiri">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-account-off-outline mdi-48px mb-2"></i>
                                                <p>Belum ada data user.</p>
                                                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="mdi mdi-plus"></i> Tambah User Pertama
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($users->hasPages())
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="user-info" class="mb-3">
                        <h6 id="user-name" class="mb-1"></h6>
                        <small class="text-muted" id="user-login-id"></small>
                    </div>
                    <div id="qrcode-container" class="d-flex justify-content-center mb-3"></div>
                    <div class="alert alert-info alert-sm">
                        <small>
                            <i class="mdi mdi-information-outline me-1"></i>
                            Gunakan QR Code ini untuk absensi
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="downloadQRCode()">
                        <i class="mdi mdi-download me-1"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        let currentQRCode = null;

        // Tangkap event saat modal akan ditampilkan
        var qrModal = document.getElementById('qrModal');
        qrModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var name = button.getAttribute('data-name');
            var loginId = button.getAttribute('data-login-id');
            var qrValue = button.getAttribute('data-qr');

            // Update informasi user
            document.getElementById('user-name').textContent = name;
            document.getElementById('user-login-id').textContent = 'ID: ' + loginId;

            // Cari container QR Code
            var qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';

            // Buat QR code baru
            currentQRCode = new QRCode(qrContainer, {
                text: qrValue,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        // Download QR Code
        function downloadQRCode() {
            const qrContainer = document.getElementById('qrcode-container');
            const userName = document.getElementById('user-name').textContent;
            const userLoginId = document.getElementById('user-login-id').textContent;

            html2canvas(qrContainer).then(canvas => {
                const link = document.createElement('a');
                link.download = `QRCode-${userName}-${userLoginId.replace('ID: ', '')}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }

        // Clear QR code ketika modal ditutup
        qrModal.addEventListener('hidden.bs.modal', function () {
            var qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';
            currentQRCode = null;
        });

        // Search functionality (jika diperlukan)
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts setelah 5 detik
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Quick search (bisa ditambahkan input search nanti)
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Cari user...';
            searchInput.className = 'form-control form-control-sm mb-3';
            searchInput.style.maxWidth = '300px';

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const name = row.cells[1].textContent.toLowerCase();
                    const loginId = row.cells[2].textContent.toLowerCase();
                    const email = row.cells[3].textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || loginId.includes(searchTerm) || email.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Tambahkan search input sebelum table
            const table = document.querySelector('.table-responsive');
            table.parentNode.insertBefore(searchInput, table);
        });
    </script>
@endpush

@push('styles')
<style>
    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }
    
    .badge-outline-primary {
        border: 1px solid #007bff;
        color: #007bff;
        background-color: transparent;
    }
    
    .badge-outline-info {
        border: 1px solid #17a2b8;
        color: #17a2b8;
        background-color: transparent;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-icon {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    /* Hover effects */
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    /* Responsive table */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .badge {
            font-size: 0.7em;
        }
        
        .btn-icon {
            width: 30px;
            height: 30px;
        }
    }
</style>
@endpush
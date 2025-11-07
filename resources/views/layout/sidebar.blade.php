<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="/">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- =================================== --}}
        {{-- MENU UNTUK ADMIN --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Menu Admin</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('divisions.index') }}">
                    <i class="menu-icon mdi mdi-sitemap"></i>
                    <span class="menu-title">Data Divisi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
            <li class="nav-item"> {{-- ADMIN BISA VERIFIKASI --}}
                <a class="nav-link" href="{{ route('audit.verify.list') }}">
                    <i class="menu-icon mdi mdi-checkbox-marked-outline"></i>
                    <span class="menu-title">Verifikasi Absensi</span>
                </a>
            </li>
            <li class="nav-item"> {{-- ADMIN BISA LIHAT IZIN --}}
                <a class="nav-link" href="{{ route('audit.late.list') }}">
                    <i class="menu-icon mdi mdi-clock-alert-outline"></i>
                    <span class="menu-title">Izin Telat Masuk</span>
                </a>
            </li>
        @endif
        {{-- =================================== --}}


        {{-- =================================== --}}
        {{-- MENU UNTUK AUDIT --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'audit')
            <li class="nav-item nav-category">Menu Audit</li>
            <li class="nav-item"> {{-- AUDIT HANYA BISA KELOLA USER & DIVISI --}}
                <a class="nav-link" href="{{ route('divisions.index') }}">
                    <i class="menu-icon mdi mdi-sitemap"></i>
                    <span class="menu-title">Data Divisi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
            <li class="nav-item"> {{-- AUDIT BISA VERIFIKASI --}}
                <a class="nav-link" href="{{ route('audit.verify.list') }}">
                    <i class="menu-icon mdi mdi-checkbox-marked-outline"></i>
                    <span class="menu-title">Verifikasi Absensi</span>
                </a>
            </li>
            <li class="nav-item"> {{-- AUDIT BISA LIHAT IZIN --}}
                <a class="nav-link" href="{{ route('audit.late.list') }}">
                    <i class="menu-icon mdi mdi-clock-alert-outline"></i>
                    <span class="menu-title">Izin Telat Masuk</span>
                </a>
            </li>
        @endif
        {{-- =================================== --}}


        {{-- =================================== --}}
        {{-- MENU UNTUK SECURITY --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'security')
            <li class="nav-item nav-category">Menu Security</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.scan') }}">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Pindai Absensi</span>
                </a>
            </li>
        @endif
        {{-- =================================== --}}

        {{-- =================================== --}}
        {{-- MENU UNTUK USER BIASA --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'user_biasa')
            <li class="nav-item nav-category">Menu User</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('my.team') }}">
                    <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                    <span class="menu-title">Tim Saya</span>
                </a>
            </li>
        @endif
        {{-- =================================== --}}
    </ul>
</nav>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="/">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- =================================== --}}
        {{-- MENU UNTUK ADMIN & AUDIT --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'audit') {{-- <-- DIUBAH DI SINI --}} <li
            class="nav-item nav-category">Menu Admin</li>
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

            {{-- (Sisa komentar Anda biarkan saja) --}}
    </ul>
</nav>

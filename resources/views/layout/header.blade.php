@php
    use Illuminate\Support\Facades\Storage;
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row w-100">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <!-- PERBAIKAN: Gunakan path absolut untuk logo -->
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="/assets/images/logo-pstore.png" alt="logo" style="width: 150px; height: auto;" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="/assets/images/logo-pstore.png" alt="logo" style="width: 45px; height: auto;" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text">@yield('heading')</h1>
                <h3 class="welcome-sub-text">{{ Auth::user()->role }} - {{ Auth::user()->division->name ?? 'N/A' }}</h3>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <!-- Fullscreen Button -->
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link" href="javascript:void(0)" onclick="toggleFullScreen()">
                    <i class="mdi mdi-fullscreen"></i> Fullscreen
                </a>
            </li>

            <!-- Search -->
            <li class="nav-item">
                <form class="search-form" action="#">
                    <i class="icon-search"></i>
                    <input type="search" class="form-control" placeholder="Search Here" title="Search here">
                </form>
            </li>

            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"></i>
                    <span class="count"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                    aria-labelledby="notificationDropdown">
                    <a class="dropdown-item py-3 border-bottom">
                        <p class="mb-0 fw-medium float-start">You have 4 new notifications </p>
                        <span class="badge badge-pill badge-primary float-end">View all</span>
                    </a>
                    <!-- Notifikasi items -->
                    <a class="dropdown-item preview-item py-3">
                        <div class="preview-thumbnail">
                            <i class="mdi mdi-alert m-auto text-primary"></i>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject fw-normal text-dark mb-1">Application Error</h6>
                            <p class="fw-light small-text mb-0"> Just now </p>
                        </div>
                    </a>
                </div>
            </li>

            <!-- Messages -->
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-mail icon-lg"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                    aria-labelledby="countDropdown">
                    <a class="dropdown-item py-3">
                        <p class="mb-0 fw-medium float-start">You have 7 unread mails </p>
                        <span class="badge badge-pill badge-primary float-end">View all</span>
                    </a>
                    <!-- Message items -->
                    <a class="dropdown-item preview-item py-3">
                        <div class="preview-thumbnail">
                            <img src="/assets/images/faces/face10.jpg" alt="image" class="img-sm profile-pic">
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject fw-normal text-dark mb-1">Message from John</h6>
                            <p class="fw-light small-text mb-0"> 1 Minutes ago </p>
                        </div>
                    </a>
                </div>
            </li>

            <!-- User Profile -->
            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <!-- LOGIKA FOTO/INISIAL (KECIL) -->
                    @if (Auth::user()->profile_photo_path && Storage::exists(Auth::user()->profile_photo_path))
                        <!-- JIKA FOTO ADA, tampilkan foto -->
                        <img class="img-xs rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image">
                    @else
                        <!-- JIKA TIDAK, tampilkan inisial -->
                        <div class="profile-initial-nav">
                            {{ getInitials(Auth::user()->name) }}
                        </div>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <!-- LOGIKA FOTO/INISIAL (BESAR) -->
                        @if (Auth::user()->profile_photo_path && Storage::exists(Auth::user()->profile_photo_path))
                            <!-- JIKA FOTO ADA, tampilkan foto -->
                            <img class="img-md rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            <!-- JIKA TIDAK, tampilkan inisial -->
                            <div class="profile-initial-dropdown mb-2">
                                {{ getInitials(Auth::user()->name) }}
                            </div>
                        @endif

                        <p class="mb-1 mt-3 fw-semibold">{{ Auth::user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                        <small class="text-muted">{{ Auth::user()->role }} -
                            {{ Auth::user()->division->name ?? 'N/A' }}</small>
                    </div>

                    <!-- Link "My Profile" -->
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile
                    </a>
                    <a class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages
                    </a>
                    <a class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Activity
                    </a>
                    <a class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> FAQ
                    </a>

                    <!-- Link "Sign Out" -->
                    <a href="{{ route('logout') }}" class="dropdown-item">
                        <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
                    </a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

<script>
    function toggleFullScreen() {
        if (!document.fullscreenElement &&
            !document.webkitFullscreenElement &&
            !document.mozFullScreenElement &&
            !document.msFullscreenElement) {
            // Enter fullscreen
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }

    // Fallback untuk ikon jika tidak load
    document.addEventListener('DOMContentLoaded', function() {
        // Cek jika ikon material design tidak load
        const icons = document.querySelectorAll('.mdi');
        icons.forEach(icon => {
            // Jika ikon tidak memiliki content, tambahkan fallback
            if (getComputedStyle(icon, '::before').content === 'none') {
                const iconName = Array.from(icon.classList).find(cls => cls.startsWith('mdi-'));
                if (iconName) {
                    const fallbackText = getIconFallback(iconName);
                    if (fallbackText) {
                        icon.innerHTML = fallbackText;
                    }
                }
            }
        });

        function getIconFallback(iconClass) {
            const fallbacks = {
                'mdi-fullscreen': '⛶',
                'mdi-account-outline': '👤',
                'mdi-message-text-outline': '💬',
                'mdi-calendar-check-outline': '📅',
                'mdi-help-circle-outline': '❓',
                'mdi-power': '🔌',
                'mdi-alert': '⚠️',
                'mdi-menu': '☰'
            };
            return fallbacks[iconClass] || '⚫';
        }
    });
</script>

<style>
    /* Profile Initial Styles */
    .profile-initial-nav {
        width: 40px;
        height: 40px;
        background: #000;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .profile-initial-nav:hover {
        background: #333;
        transform: scale(1.05);
    }

    .profile-initial-dropdown {
        width: 60px;
        height: 60px;
        background: #000;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
        margin: 0 auto;
    }

    /* Navbar Brand Styles */
    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    /* Navigation Link Styles */
    .nav-link {
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: #000 !important;
    }

    /* Dropdown Styles */
    .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.08);
    }

    .dropdown-item {
        border-radius: 6px;
        margin: 2px 8px;
        transition: all 0.3s ease;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
        color: #000;
    }

    /* Search Form Styles */
    .search-form .form-control {
        border-radius: 20px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .search-form .form-control:focus {
        border-color: #000;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }

    /* Fallback untuk gambar profil di messages */
    .profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #666;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .profile-initial-nav {
            width: 35px;
            height: 35px;
            font-size: 12px;
        }
        
        .navbar-brand img {
            width: 120px !important;
        }
        
        .navbar-brand-mini img {
            width: 35px !important;
        }
    }

    /* Loading state untuk gambar */
    img {
        transition: opacity 0.3s ease;
    }

    img[src=""], img:not([src]) {
        opacity: 0;
    }
</style>
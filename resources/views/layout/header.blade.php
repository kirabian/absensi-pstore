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
            {{-- Arahkan logo ke Dashboard --}}
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 150px; height: auto;" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo" style="width: 45px; height: auto;" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                {{-- Dibuat Dinamis --}}
                <h1 class="welcome-text">@yield('heading')</h1>
                <h3 class="welcome-sub-text">{{ Auth::user()->role }} - {{ Auth::user()->division->name ?? 'N/A' }}</h3>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            {{-- Fullscreen Button --}}
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link" href="javascript:void(0)" onclick="toggleFullScreen()">
                    <i class="mdi mdi-fullscreen"></i> Fullscreen
                </a>
            </li>

            {{-- Search -- Hanya untuk Admin --}}
            @if(auth()->user()->role == 'admin')
            <li class="nav-item">
                <div class="search-form">
                    <i class="icon-search"></i>
                    <input type="search" class="form-control" id="globalSearch"
                        placeholder="Search users, broadcasts, divisions..." title="Search here" autocomplete="off">
                    <div class="search-results dropdown-menu" id="searchResults" style="display: none;"></div>
                </div>
            </li>
            @endif

            {{-- Notifications --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"></i>
                    <span class="count">4</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                    aria-labelledby="notificationDropdown">
                    <a class="dropdown-item py-3 border-bottom">
                        <p class="mb-0 fw-medium float-start">You have 4 new notifications</p>
                        <span class="badge badge-pill badge-primary float-end">View all</span>
                    </a>
                    <a class="dropdown-item preview-item py-3">
                        <div class="preview-thumbnail">
                            <i class="mdi mdi-alert m-auto text-primary"></i>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject fw-normal text-dark mb-1">Application Error</h6>
                            <p class="fw-light small-text mb-0">Just now</p>
                        </div>
                    </a>
                    <a class="dropdown-item preview-item py-3">
                        <div class="preview-thumbnail">
                            <i class="mdi mdi-settings m-auto text-primary"></i>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject fw-normal text-dark mb-1">Settings</h6>
                            <p class="fw-light small-text mb-0">Private message</p>
                        </div>
                    </a>
                    <a class="dropdown-item preview-item py-3">
                        <div class="preview-thumbnail">
                            <i class="mdi mdi-airballoon m-auto text-primary"></i>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject fw-normal text-dark mb-1">New user registration</h6>
                            <p class="fw-light small-text mb-0">2 days ago</p>
                        </div>
                    </a>
                </div>
            </li>

            {{-- Messages --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-mail icon-lg"></i>
                    <span class="count">7</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                    aria-labelledby="countDropdown">
                    <a class="dropdown-item py-3">
                        <p class="mb-0 fw-medium float-start">You have 7 unread mails</p>
                        <span class="badge badge-pill badge-primary float-end">View all</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <img src="{{ asset('assets/images/faces/face10.jpg') }}" alt="image" class="profile-pic">
                        </div>
                        <div class="preview-item-content flex-grow py-2">
                            <h6 class="preview-subject fw-normal text-dark mb-1">Meeting scheduled</h6>
                            <p class="fw-light small-text mb-0">The meeting is scheduled for 3 PM</p>
                        </div>
                    </a>
                    <a class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <img src="{{ asset('assets/images/faces/face12.jpg') }}" alt="image" class="profile-pic">
                        </div>
                        <div class="preview-item-content flex-grow py-2">
                            <h6 class="preview-subject fw-normal text-dark mb-1">New message</h6>
                            <p class="fw-light small-text mb-0">You have a new message from John</p>
                        </div>
                    </a>
                </div>
            </li>

            {{-- User Profile --}}
            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    {{-- =================================== --}}
                    {{--   LOGIKA FOTO/INISIAL (KECIL)   --}}
                    {{-- =================================== --}}
                    @if (Auth::user()->profile_photo_path)
                        {{-- JIKA FOTO ADA, tampilkan foto --}}
                        <img class="img-xs rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                            alt="Profile image">
                    @else
                        {{-- JIKA TIDAK, tampilkan inisial --}}
                        <div class="profile-initial-nav">
                            {{ getInitials(Auth::user()->name) }}
                        </div>
                    @endif
                    {{-- =================================== --}}
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        {{-- =================================== --}}
                        {{--   LOGIKA FOTO/INISIAL (BESAR)   --}}
                        {{-- =================================== --}}
                        @if (Auth::user()->profile_photo_path)
                            {{-- JIKA FOTO ADA, tampilkan foto --}}
                            <img class="img-md rounded-circle"
                                src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image"
                                style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            {{-- JIKA TIDAK, tampilkan inisial --}}
                            <div class="profile-initial-dropdown mb-2">
                                {{ getInitials(Auth::user()->name) }}
                            </div>
                        @endif
                        {{-- =================================== --}}

                        <p class="mb-1 mt-3 fw-semibold">{{ Auth::user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                        <small class="text-muted">{{ Auth::user()->role }} -
                            {{ Auth::user()->division->name ?? 'N/A' }}</small>
                    </div>

                    {{-- Link "My Profile" --}}
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

                    <div class="dropdown-divider"></div>

                    {{-- Link "Sign Out" --}}
                    <a href="{{ route('logout') }}" class="dropdown-item" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
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
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.webkitRequestFullscreen) { // Safari
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) { // Firefox
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.msRequestFullscreen) { // IE/Edge
                document.documentElement.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) { // Safari
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) { // Firefox
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) { // IE/Edge
                document.msExitFullscreen();
            }
        }
    }
</script>

<style>
    /* Search Form Styles */
    .search-form {
        position: relative;
        margin-right: 15px;
    }

    .search-form .icon-search {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
    }

    .search-form .form-control {
        border-radius: 25px;
        border: 2px solid #e2e8f0;
        padding-left: 40px;
        padding-right: 15px;
        transition: all 0.3s ease;
        background: #f8f9fa;
        width: 300px;
    }

    .search-form .form-control:focus {
        border-color: #000;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        background: white;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        margin-top: 5px;
        display: none;
    }

    .search-results .dropdown-item {
        border-bottom: 1px solid #f8f9fa;
        padding: 12px 15px;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #333;
    }

    .search-results .dropdown-item:last-child {
        border-bottom: none;
    }

    .search-results .dropdown-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
        color: #000;
    }

    .search-results .dropdown-item:focus {
        background-color: #e9ecef;
        outline: none;
    }

    .search-results .badge {
        font-size: 0.7em;
        padding: 4px 8px;
        border-radius: 4px;
    }

    /* Animasi untuk search results */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .search-results {
        animation: slideDown 0.2s ease-out;
    }

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
        border: 2px solid transparent;
    }

    .profile-initial-nav:hover {
        background: #333;
        transform: scale(1.05);
        border-color: #000;
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
        border: 3px solid #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    /* General Navbar Styles */
    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    .nav-link {
        transition: color 0.3s ease;
        color: #6c757d !important;
    }

    .nav-link:hover {
        color: #000 !important;
    }

    .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.08);
        padding: 10px 0;
    }

    .dropdown-item {
        border-radius: 6px;
        margin: 2px 8px;
        transition: all 0.3s ease;
        padding: 8px 16px;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
        color: #000;
        transform: translateX(5px);
    }

    .dropdown-header {
        padding: 15px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px 8px 0 0;
    }

    /* Notification and Message Styles */
    .count-indicator {
        position: relative;
    }

    .count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .preview-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #f8f9fa;
    }

    .preview-item:last-child {
        border-bottom: none;
    }

    .preview-thumbnail {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        background: #f8f9fa;
    }

    .preview-item-content {
        flex: 1;
    }

    .preview-subject {
        font-size: 14px;
        margin-bottom: 2px;
    }

    .small-text {
        font-size: 12px;
        color: #6c757d;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .search-form {
            margin: 10px 0;
            width: 100%;
        }
        
        .search-form .form-control {
            width: 100%;
        }
        
        .search-results {
            position: fixed;
            left: 10px;
            right: 10px;
            width: auto !important;
        }

        .navbar-menu-wrapper {
            padding: 10px 0;
        }

        .welcome-text {
            font-size: 1.2rem;
        }

        .welcome-sub-text {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .search-form .form-control {
            width: 200px;
        }
    }

    /* Loading animation */
    .mdi-spin {
        animation: mdi-spin 1s infinite linear;
    }

    @keyframes mdi-spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(359deg);
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    
    // Jika search input tidak ada (bukan admin), skip
    if (!searchInput) return;
    
    let searchTimeout;

    // Ikon berdasarkan tipe
    const typeIcons = {
        'user': 'mdi-account',
        'broadcast': 'mdi-bullhorn',
        'division': 'mdi-sitemap',
        'branch': 'mdi-office-building'
    };

    // Warna berdasarkan tipe
    const typeColors = {
        'user': 'text-primary',
        'broadcast': 'text-warning',
        'division': 'text-info',
        'branch': 'text-success'
    };

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length < 2) {
            hideResults();
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            hideResults();
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideResults();
            searchInput.blur();
        }
        
        // Enter key - go to first result
        if (e.key === 'Enter' && searchResults.style.display === 'block') {
            const firstResult = searchResults.querySelector('a');
            if (firstResult) {
                e.preventDefault();
                firstResult.click();
            }
        }
        
        // Arrow down - focus first result
        if (e.key === 'ArrowDown' && searchResults.style.display === 'block') {
            e.preventDefault();
            const firstResult = searchResults.querySelector('a');
            if (firstResult) {
                firstResult.focus();
            }
        }
    });

    // Keyboard navigation for results
    searchResults.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const next = e.target.nextElementSibling || searchResults.querySelector('a');
            if (next) next.focus();
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prev = e.target.previousElementSibling || searchInput;
            if (prev) prev.focus();
        }
        if (e.key === 'Escape') {
            hideResults();
            searchInput.focus();
        }
    });

    function performSearch(query) {
        // Show loading state
        searchResults.innerHTML = `
            <div class="dropdown-item text-muted">
                <i class="mdi mdi-loading mdi-spin me-2"></i>Searching...
            </div>
        `;
        showResults();

        fetch(`/search?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayResults(data.results);
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = `
                    <div class="dropdown-item text-danger">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>Search failed. Please try again.
                    </div>
                `;
                showResults();
            });
    }

    function displayResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="dropdown-item text-muted">
                    <i class="mdi mdi-magnify me-2"></i>No results found for "<strong>${searchInput.value}</strong>"
                </div>
            `;
            showResults();
            return;
        }

        const resultsHtml = results.map(result => `
            <a class="dropdown-item d-flex align-items-center py-2" href="${result.url}" tabindex="0">
                <div class="me-3 ${typeColors[result.type]}">
                    <i class="mdi ${result.icon}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-medium">${result.title}</div>
                    <small class="text-muted">${result.description}</small>
                </div>
                <span class="badge bg-light text-dark small text-uppercase">${result.type}</span>
            </a>
        `).join('');

        searchResults.innerHTML = resultsHtml;
        showResults();
    }

    function showResults() {
        searchResults.style.display = 'block';
        searchResults.style.width = searchInput.offsetWidth + 'px';
        searchResults.style.maxHeight = '400px';
        searchResults.style.overflowY = 'auto';
        
        // Position the results dropdown
        const searchForm = searchInput.closest('.search-form');
        const rect = searchForm.getBoundingClientRect();
        searchResults.style.left = '0';
        searchResults.style.top = '100%';
    }

    function hideResults() {
        searchResults.style.display = 'none';
    }

    // Add focus effect to search input
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
    });

    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('focused');
    });
});
</script>
@endpush
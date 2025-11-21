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
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo"
                    style="width: 150px; height: auto;" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo"
                    style="width: 45px; height: auto;" />
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
            {{-- Fullscreen Button --}}
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link" href="javascript:void(0)" onclick="toggleFullScreen()">
                    <i class="mdi mdi-fullscreen"></i> Fullscreen
                </a>
            </li>

            {{-- Search -- Hanya untuk Admin --}}
            @if (auth()->user()->role == 'admin')
                <li class="nav-item">
                    <div class="search-form position-relative">
                        <i class="icon-search position-absolute search-icon"></i>
                        <input type="search" class="form-control search-input" id="globalSearch"
                            data-url="{{ route('search') }}" placeholder="Search users, broadcasts..."
                            autocomplete="off">
                        <div class="search-results dropdown-menu" id="searchResults"></div>
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
                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    @if (Auth::user()->profile_photo_path)
                        <img class="img-xs rounded-circle" src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                            alt="Profile image">
                    @else
                        <div class="profile-initial-nav">
                            {{ getInitials(Auth::user()->name) }}
                        </div>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        @if (Auth::user()->profile_photo_path)
                            <img class="img-md rounded-circle"
                                src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image"
                                style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            <div class="profile-initial-dropdown mb-2">
                                {{ getInitials(Auth::user()->name) }}
                            </div>
                        @endif

                        <p class="mb-1 mt-3 fw-semibold">{{ Auth::user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                        <small class="text-muted">{{ Auth::user()->role }} -
                            {{ Auth::user()->division->name ?? 'N/A' }}</small>
                    </div>

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
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
        } else {
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
</script>

<style>
    /* Search Form Styles */
    .search-form {
        position: relative;
        margin-right: 15px;
    }

    .search-icon {
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
        pointer-events: none;
    }

    .search-input {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 8px 15px 8px 40px;
        background: #f8f9fa;
        width: 300px;
        height: 38px;
        font-size: 14px;
    }

    .search-input:focus {
        border-color: #000;
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
        background: white;
        outline: none;
    }

    .search-results {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        max-height: 400px;
        overflow-y: auto;
        display: none;
    }

    .search-results.show {
        display: block;
    }

    .search-results .dropdown-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
    }

    .search-results .dropdown-item:last-child {
        border-bottom: none;
    }

    .search-results .dropdown-item:hover {
        background-color: #f8f9fa;
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
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Badge Styles */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }

    /* Result Item Styles */
    .search-results .mdi {
        font-size: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .search-form {
            margin: 10px 0;
            width: 100%;
        }

        .search-input {
            width: 100%;
        }
    }

    /* Scrollbar */
    .search-results::-webkit-scrollbar {
        width: 6px;
    }

    .search-results::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .search-results::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .search-results::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('globalSearch');
            const searchResults = document.getElementById('searchResults');

            if (!searchInput) return;

            let searchTimeout;

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

            // Hide on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideResults();
                }
            });

            function performSearch(query) {
                searchResults.innerHTML = `
                    <div class="dropdown-item text-muted">
                        <i class="mdi mdi-loading mdi-spin me-2"></i>Searching...
                    </div>
                `;
                showResults();

                const url = searchInput.getAttribute('data-url');

                fetch(`${url}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error("Backend Error:", data.error);
                            throw new Error(data.error);
                        }
                        displayResults(data.results);
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        searchResults.innerHTML = `
                            <div class="dropdown-item text-danger">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>Error: Failed to load data.
                            </div>
                        `;
                        showResults();
                    });
            }

            function displayResults(results) {
                if (results.length === 0) {
                    searchResults.innerHTML = `
                        <div class="dropdown-item text-muted">
                            <i class="mdi mdi-magnify me-2"></i>No results found for "<strong>${escapeHtml(searchInput.value)}</strong>"
                        </div>
                    `;
                    showResults();
                    return;
                }

                const resultsHtml = results.map(result => `
                    <a class="dropdown-item d-flex align-items-center" href="${escapeHtml(result.url)}" tabindex="0">
                        <div class="me-3 ${getTypeClass(result.type)}">
                            <i class="mdi ${escapeHtml(result.icon)}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">${escapeHtml(result.title)}</div>
                            <small class="text-muted">${escapeHtml(result.description)}</small>
                        </div>
                        <span class="badge bg-light text-dark small text-uppercase">${escapeHtml(result.type)}</span>
                    </a>
                `).join('');

                searchResults.innerHTML = resultsHtml;
                showResults();
            }

            function getTypeClass(type) {
                const typeClasses = {
                    'user': 'text-primary',
                    'broadcast': 'text-warning',
                    'division': 'text-info'
                };
                return typeClasses[type] || 'text-success';
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function showResults() {
                searchResults.classList.add('show');
                searchResults.style.width = searchInput.offsetWidth + 'px';
            }

            function hideResults() {
                searchResults.classList.remove('show');
            }
        });
    </script>
@endpush
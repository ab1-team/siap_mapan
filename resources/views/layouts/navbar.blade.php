@php
    $logo = Auth::user()->foto;
    if ($logo == 'no_image.png') {
        $logo = '/assets/img/' . $logo;
    } else {
        $logo = '/storage/profil/' . $logo;
    }
@endphp

<style>
    /* =========================================================
       Navbar — buka/tutup sidebar & user menu
       Warna abu-abu muda (light gray) yang ringan dan matching sidebar.
       ========================================================= */

    /* Variabel warna navbar */
    .navbar.topbar.bg-navbar {
        /* Gradient abu-abu muda: terang di atas, sedikit lebih gelap di bawah */
        --nb-bg-start: #f1f3f7;
        --nb-bg-mid: #e8ebf0;
        --nb-bg-end: #dde0e6;
        --nb-border: #c9cdd5;
        --nb-shadow: 0 .15rem 1rem rgba(0, 0, 0, .08);
        background: linear-gradient(180deg,
            var(--nb-bg-start) 0%,
            var(--nb-bg-mid) 50%,
            var(--nb-bg-end) 100%) !important;
        border: none;
        border-bottom: 1px solid var(--nb-border);
    }

    /* Tombol toggle sidebar (hamburger) */
    .topbar #sidebarToggleTop {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        height: 2.75rem !important;
        width: 2.75rem !important;
        padding: 0 !important;
        background-color: #ffffff;
        color: #4a5073;
        border: 1px solid #d3d7df;
        border-radius: 50% !important;
        box-shadow: 0 .1rem .25rem rgba(0, 0, 0, .08) !important;
        cursor: pointer;
        transition: background-color .15s ease-in-out,
            border-color .15s ease-in-out, transform .2s ease-in-out;
        margin-right: .75rem !important;
    }
    .topbar #sidebarToggleTop:hover,
    .topbar #sidebarToggleTop:focus {
        background-color: #f5f7fa !important;
        border-color: #b9bfc9 !important;
        outline: none;
        text-decoration: none;
    }
    .topbar #sidebarToggleTop:active {
        background-color: #e6eaf0 !important;
    }
    .topbar #sidebarToggleTop i {
        color: #4a5073;
        font-size: 1rem;
        transition: transform .2s ease-in-out;
    }
    body.sidebar-toggled #sidebarToggleTop i.fa-bars,
    body.sidebar-mobile-open #sidebarToggleTop i.fa-bars {
        transform: rotate(90deg);
    }

    /* Avatar & dropdown user di navbar */
    .topbar .nav-item.dropdown .nav-link {
        padding: 0 .75rem !important;
    }
    .topbar .img-profile {
        height: 2.25rem;
        width: 2.25rem;
        max-width: 2.25rem !important;
        object-fit: cover;
        border: 2px solid #ffffff;
        box-shadow: 0 .15rem .35rem rgba(0, 0, 0, .15);
    }
    .topbar .nav-item .NamaUser {
        color: #2e3142;
        font-weight: 600;
        letter-spacing: .01em;
        max-width: 12rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }

    /* Topbar divider: abu-abu medium supaya kontras dengan bg terang */
    .topbar .topbar-divider {
        border-right: 1px solid #c9cdd5 !important;
    }

    /* Nav-link di topbar (klik area user dropdown) */
    .topbar .navbar-nav .nav-link {
        color: #4a5073;
    }
    .topbar .navbar-nav .nav-link:hover {
        color: #1a1f2e;
    }

    /* Shadow topbar: lebih halus untuk bg terang */
    .navbar.topbar {
        box-shadow: 0 .15rem .85rem rgba(0, 0, 0, .08) !important;
    }

    /* Mobile adjustments untuk navbar */
    @media (max-width: 768px) {
        .navbar.topbar {
            padding-left: .65rem !important;
            padding-right: .65rem !important;
        }
        .navbar.topbar .navbar-nav {
            margin-left: auto !important;
        }
        .topbar #sidebarToggleTop {
            height: 2.4rem !important;
            width: 2.4rem !important;
            margin-right: .35rem !important;
        }
        .topbar #sidebarToggleTop i {
            font-size: .95rem;
        }
        /* Sembunyikan nama user pada layar kecil (hanya foto) */
        .topbar .NamaUser {
            display: none !important;
        }
        /* Topbar divider tidak relevan di mobile */
        .topbar .topbar-divider {
            display: none !important;
        }
        /* Dropdown user menyesuaikan lebar layar */
        .topbar .dropdown .dropdown-menu {
            right: .35rem !important;
            left: auto !important;
            width: calc(100vw - 1rem);
            max-width: 16rem;
        }
    }

    @media (max-width: 380px) {
        .topbar .img-profile {
            height: 2rem;
            width: 2rem;
            max-width: 2rem !important;
        }
    }

    /* Margin default container di navbar */
    .navbar-nav.ml-auto {
        margin-left: auto !important;
    }
</style>

<nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle" type="button"
        aria-label="Buka/tutup sidebar" aria-controls="accordionSidebar" aria-expanded="false">
        <i class="fa fa-bars"></i>
    </button>
    <ul class="navbar-nav ml-auto">
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle" src="{{ $logo }}"
                    alt="Foto {{ Auth::user()->nama }}">
                <span class="ml-2 d-none d-lg-inline small NamaUser">{{ Auth::user()->nama }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="/profil">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="" id="logoutButton">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

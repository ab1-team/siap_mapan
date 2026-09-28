<style>
    /* =========================================================
       Sidebar — Tata letak modern & clean (SECONDARY DARK THEME)
       Warna netral gelap (gunmetal/abu-abu gelap) ala SB Admin 2,
       dikombinasikan dengan aksen biru brand untuk highlight aktif.
       ========================================================= */

    /* ---------- Variabel warna sidebar (SECONDARY DARK) ---------- */
    .sidebar-wrapper {
        /* Background: gradient abu-abu gelap (gunmetal) — netral & elegan */
        --sb-bg: linear-gradient(180deg, #4a4d58 0%, #3a3d47 50%, #2e313a 100%);
        --sb-bg-solid: #3a3d47;
        /* Border & divider: putih semi-transparan untuk kedalaman */
        --sb-border: rgba(255, 255, 255, .08);
        /* Warna teks idle */
        --sb-text: #c5c7d0;
        --sb-text-muted: #9ea1ad;
        --sb-text-strong: #ffffff;
        /* Hover */
        --sb-hover-bg: rgba(255, 255, 255, .08);
        --sb-hover-text: #ffffff;
        /* Active: gradient biru brand sebagai highlight */
        --sb-active-bg: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        --sb-active-text: #ffffff;
        /* Heading section */
        --sb-heading: rgba(255, 255, 255, .45);
        --sb-divider: rgba(255, 255, 255, .08);
        /* Sub-menu indikator bulat */
        --sb-sub-indicator: rgba(255, 255, 255, .3);
        --sb-sub-indicator-hover: #ffffff;
        /* Brand border */
        --sb-brand-border: rgba(255, 255, 255, .12);
        /* Shadow */
        --sb-shadow: 0 .5rem 2rem rgba(0, 0, 0, .4);
        /* Scrollbar */
        --sb-scrollbar: rgba(255, 255, 255, .15);
        --sb-scrollbar-hover: rgba(255, 255, 255, .3);
    }

    /* ---------- Tipografi dasar ---------- */
    .sidebar .nav-link span,
    .sidebar .collapse-inner .collapse-item,
    .sidebar .collapse-inner .dropdown-item {
        font-weight: 500 !important;
        letter-spacing: .01em;
    }

    /* ---------- Wrapper sidebar ---------- */
    .sidebar-wrapper {
        height: 100vh;
        max-height: 100vh;
        min-height: 0;
        min-width: 0; /* penting: agar flex item bisa shrink dari konten besar */
        flex-shrink: 0; /* jangan sampai di-compress jadi 0 */
        width: 16rem; /* dikecilkan dari 18rem (288px) → 16rem (256px) */
        max-width: 16rem;
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
        overflow: hidden;
        background: var(--sb-bg);
        border-right: 1px solid var(--sb-border);
        transition: width .25s ease-in-out, max-width .25s ease-in-out,
            transform .25s ease-in-out, margin-left .25s ease-in-out;
        will-change: width, transform;
        box-shadow: var(--sb-shadow);
    }

    /* Override width .sidebar (ul) yang dipaksa 18rem oleh custom.css */
    .sidebar-wrapper .sidebar {
        width: 100% !important;
        max-width: 100% !important;
        background: transparent !important;
    }

    .sidebar-wrapper > .sidebar-brand {
        flex-shrink: 0;
        position: relative;
        z-index: 3;
        background: transparent;
        border-bottom: 1px solid var(--sb-brand-border);
    }

    /* ---------- Area scroll menu ---------- */
    #accordionSidebar {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        margin: 0;
        padding: .75rem 0 1.25rem;
    }

    #accordionSidebar > .sidebar-heading,
    #accordionSidebar > .sidebar-divider {
        flex-shrink: 0;
    }

    /* Scrollbar tipis & halus (dark theme) */
    #accordionSidebar::-webkit-scrollbar {
        width: 6px;
    }
    #accordionSidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    #accordionSidebar::-webkit-scrollbar-thumb {
        background-color: var(--sb-scrollbar);
        border-radius: 3px;
    }
    #accordionSidebar::-webkit-scrollbar-thumb:hover {
        background-color: var(--sb-scrollbar-hover);
    }
    #accordionSidebar {
        scrollbar-width: thin;
        scrollbar-color: var(--sb-scrollbar) transparent;
    }

    /* ---------- Brand / Header ---------- */
    /* Selector specificity tinggi untuk mengalahkan custom.css & template */
    .sidebar .sidebar-brand,
    .sidebar-wrapper > .sidebar-brand,
    a.sidebar-brand {
        padding-top: 1.25rem !important;
        padding-right: 1rem !important;
        padding-left: 1rem !important;
        padding-bottom: 1.25rem !important;
        height: auto !important;
        min-height: 9rem !important;
        margin-bottom: 0 !important;
        margin-top: 0 !important;
        border-bottom: 1px solid var(--sb-brand-border);
        text-align: center;
        gap: .5rem;
        box-sizing: border-box;
        transition: padding .25s ease-in-out, min-height .25s ease-in-out;
    }
    .sidebar .sidebar-brand .sidebar-brand-text {
        font-weight: 900 !important;
        font-size: 1.05rem !important;
        color: var(--sb-text-strong) !important;
        letter-spacing: .025em;
        line-height: 1.3;
        text-align: center;
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
        text-transform: none;
        font-style: normal;
        transition: opacity .2s ease-in-out, font-size .2s ease-in-out;
    }

    /* Pengaman: pastikan teks di dalam <b> benar-benar bold + putih */
    .sidebar .sidebar-brand .sidebar-brand-text,
    .sidebar .sidebar-brand .sidebar-brand-text b,
    .sidebar .sidebar-brand .sidebar-brand-text strong {
        color: var(--sb-text-strong) !important;
        font-weight: 900 !important;
    }

    /* Cegah link parent mengubah warna teks brand */
    .sidebar .sidebar-brand,
    .sidebar .sidebar-brand:hover,
    .sidebar .sidebar-brand:focus,
    .sidebar .sidebar-brand:active,
    a.sidebar-brand,
    a.sidebar-brand:hover,
    a.sidebar-brand:focus,
    a.sidebar-brand:active {
        color: var(--sb-text-strong) !important;
        text-decoration: none !important;
    }

    /* Logo lingkaran di pojok atas sidebar (di atas judul nama usaha)
       Scope dibatasi hanya di dalam .sidebar-wrapper supaya tidak
       bocor ke navbar/avatar element lain di halaman.
       Container SELALU berbentuk lingkaran sempurna, baik ada gambar
       (img) maupun tidak (fallback inisial). */
    .sidebar-wrapper .sidebar-brand .sidebar-brand-icon {
        flex-shrink: 0;
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 6.5rem !important;
        height: 6.5rem !important;
        min-width: 6.5rem;
        min-height: 6.5rem;
        aspect-ratio: 1 / 1; /* safety: pastikan selalu square */
        margin: 0 0 .6rem 0 !important;
        margin-top: 0.25rem !important;
        padding: 0 !important;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        overflow: hidden;
        /* Logo: gradient putih→biru muda supaya kontras di atas bg biru tua */
        background: linear-gradient(135deg, #ffffff 0%, #dbeafe 100%) !important;
        box-shadow: 0 .45rem 1.5rem rgba(0, 0, 0, .35),
            inset 0 0 0 4px rgba(255, 255, 255, .25);
        position: relative;
        text-decoration: none !important;
        color: #1e40af;
        transition: width .2s ease-in-out, height .2s ease-in-out,
            min-width .2s ease-in-out, min-height .2s ease-in-out,
            margin .2s ease-in-out;
    }

    /* Image di dalam container: ikut melengkung mengikuti lingkaran */
    .sidebar-wrapper .sidebar-brand .sidebar-brand-icon img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover;
        border-radius: 50% !important;
        -webkit-border-radius: 50% !important;
        display: block;
        background: linear-gradient(135deg, #ffffff 0%, #dbeafe 100%);
    }

    /* Inisial nama usaha (fallback jika logo gagal load) */
    .sidebar-wrapper .sidebar-brand .sidebar-brand-icon-fallback {
        color: #1e40af;
        font-weight: 800;
        font-size: 2.4rem;
        letter-spacing: .02em;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        transition: font-size .2s ease-in-out;
    }

    /* ---------- Item menu (jarak lebih lega) ---------- */
    .sidebar .nav-item {
        margin: 0 .5rem;
        position: relative;
    }
    .sidebar .nav-item + .nav-item {
        margin-top: 4px;
    }
    .sidebar .nav-item:last-child {
        margin-bottom: .5rem;
    }
    .sidebar .nav-item:has(> .collapse) {
        margin-top: 4px;
    }

    /* ---------- Nav link utama ----------
       Specificity disejajarkan dengan template SB Admin 2
       dan !important dipakai hanya untuk properti yang bentrok. */
    .sidebar .nav-item .nav-link {
        padding: .65rem .9rem !important;
        margin: 0 !important;
        width: auto !important;
        border-radius: .5rem !important;
        color: var(--sb-text);
        transition: all .2s ease-in-out;
        display: flex !important;
        align-items: center;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
    }
    .sidebar .nav-item .nav-link i {
        font-size: .9rem !important;
        margin-right: .75rem !important;
        width: 1.25rem;
        text-align: center;
        color: var(--sb-text-muted);
        transition: color .2s ease-in-out, margin-right .2s ease-in-out;
        flex-shrink: 0;
    }
    .sidebar .nav-item .nav-link span {
        font-size: .875rem;
        display: inline !important;
        flex: 1;
        opacity: 1;
        color: inherit;
        transition: opacity .15s ease-in-out;
    }
    .sidebar .nav-item .nav-link:hover {
        background-color: var(--sb-hover-bg) !important;
        color: var(--sb-hover-text) !important;
    }
    .sidebar .nav-item .nav-link:hover i {
        color: #93c5fd; /* biru muda, kontras dengan dark bg */
    }

    /* Indikator panah collapse (rotate saat expanded) */
    .sidebar .nav-item .nav-link[data-toggle="collapse"]::after {
        transition: transform .2s ease-in-out, opacity .15s ease-in-out;
        flex-shrink: 0;
        color: var(--sb-text-muted);
    }
    .sidebar .nav-item .nav-link[aria-expanded="true"][data-toggle="collapse"]::after {
        transform: rotate(180deg);
    }

    /* ---------- Divider (tidak dipakai antar menu, tapi tetap tersedia) ---------- */
    .sidebar hr.sidebar-divider {
        margin: .85rem 1.1rem;
        border-top: 1px solid var(--sb-divider);
        display: none; /* sembunyikan divider antar menu */
    }

    /* ---------- Heading section (jika ada) ---------- */
    .sidebar .sidebar-heading {
        padding: 0 1.1rem;
        margin: 1rem 0 .35rem;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--sb-heading);
    }

    /* ---------- Container collapse (submenu wrapper) ---------- */
    .sidebar .nav-item .collapse,
    .sidebar .nav-item .collapsing {
        margin: 0 !important;
    }
    .sidebar .nav-item .collapse .collapse-inner,
    .sidebar .nav-item .collapsing .collapse-inner {
        padding: .35rem 0 .6rem !important;
        margin: 0 0 .25rem 0 !important;
        box-shadow: none !important;
        background-color: transparent !important;
    }

    /* ---------- Submenu item (tree-view modern) ---------- */
    /* Selector pakai specificity sama dengan template SB Admin 2
       (.sidebar .nav-item .collapse .collapse-inner .collapse-item = 6 class)
       supaya style kita menang tanpa !important */
    .sidebar .nav-item .collapse .collapse-inner .collapse-item,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item {
        padding: .55rem .9rem .55rem 2.75rem !important;
        margin: 2px .5rem !important;
        font-size: .835rem !important;
        border-radius: .45rem !important;
        transition: all .2s ease-in-out;
        display: block;
        color: var(--sb-text-muted);
        text-decoration: none;
        position: relative;
        line-height: 1.4;
        white-space: normal !important;
        overflow: hidden;
        text-overflow: ellipsis;
        word-wrap: break-word;
        word-break: break-word;
        hyphens: auto;
    }

    /* Garis indikator tree-view di kiri */
    .sidebar .nav-item .collapse .collapse-inner .collapse-item::before,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item::before,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item::before,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item::before {
        content: '';
        position: absolute;
        left: 1.5rem;
        top: 1.05rem;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: var(--sb-sub-indicator);
        transition: all .2s ease-in-out;
        flex-shrink: 0;
    }

    .sidebar .nav-item .collapse .collapse-inner .collapse-item:hover,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item:hover,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item:hover,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item:hover {
        background-color: var(--sb-hover-bg) !important;
        color: var(--sb-hover-text) !important;
    }
    .sidebar .nav-item .collapse .collapse-inner .collapse-item:hover::before,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item:hover::before,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item:hover::before,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item:hover::before {
        background-color: var(--sb-sub-indicator-hover);
    }

    /* Override Bootstrap dropdown-item agar tidak absolute */
    .sidebar .collapse-inner a.collapse-item,
    .sidebar .collapse-inner a.dropdown-item {
        background-color: transparent !important;
        border: 0 !important;
        width: auto !important;
        white-space: normal !important;
    }

    /* ---------- Menu aktif (highlight tegas) ---------- */
    .sidebar .nav-item.active .nav-link,
    .sidebar .nav-item.active > .nav-link {
        color: var(--sb-active-text) !important;
        background: var(--sb-active-bg) !important;
        font-weight: 600 !important;
        box-shadow: 0 .25rem 1rem 0 rgba(78, 115, 223, .45);
        border-radius: .5rem !important;
    }
    .sidebar .nav-item.active .nav-link i,
    .sidebar .nav-item.active > .nav-link i {
        color: #ffffff !important;
    }
    .sidebar .nav-item.active .nav-link:hover,
    .sidebar .nav-item.active > .nav-link:hover {
        filter: brightness(1.08);
    }

    /* Submenu aktif — specificity disejajarkan dengan template SB Admin 2 */
    .sidebar .nav-item .collapse .collapse-inner .collapse-item.active,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item.active,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item.active,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item.active {
        color: var(--sb-active-text) !important;
        background: var(--sb-active-bg) !important;
        font-weight: 600 !important;
        box-shadow: 0 .15rem .6rem 0 rgba(78, 115, 223, .4);
    }
    .sidebar .nav-item .collapse .collapse-inner .collapse-item.active::before,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item.active::before,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item.active::before,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item.active::before {
        background-color: #ffffff !important;
        width: 8px;
        height: 8px;
        left: 1.48rem;
        top: 1.05rem;
        transform: none;
    }

    /* Parent menu yang punya child aktif (expanded) */
    .sidebar .nav-item .nav-link[aria-expanded="true"] {
        color: #93c5fd !important;
        background-color: rgba(96, 165, 250, .12) !important;
        font-weight: 600 !important;
        border-radius: .5rem !important;
    }
    .sidebar .nav-item .nav-link[aria-expanded="true"] i {
        color: #93c5fd !important;
    }
    .sidebar .nav-item .nav-link[aria-expanded="true"]:hover {
        background-color: rgba(96, 165, 250, .2) !important;
    }

    /* Hapus <br> bawaan blade */
    .sidebar > br {
        display: none;
    }

    /* =========================================================
       DESKTOP: Collapse jadi icon-only (icon rail)
       Dipicu oleh class .sidebar-toggled di <body>
       ========================================================= */
    @media (min-width: 769px) {
        body.sidebar-toggled .sidebar-wrapper {
            width: 4.5rem !important;
            max-width: 4.5rem !important;
        }

        /* Brand: sembunyikan teks & perkecil logo jadi inisial */
        body.sidebar-toggled .sidebar-wrapper .sidebar-brand {
            padding-top: .85rem !important;
            padding-bottom: .85rem !important;
            padding-left: .25rem !important;
            padding-right: .25rem !important;
            min-height: 4.5rem !important;
            justify-content: center !important;
            gap: 0 !important;
        }
        body.sidebar-toggled .sidebar-wrapper .sidebar-brand-icon {
            width: 2.5rem !important;
            height: 2.5rem !important;
            min-width: 2.5rem !important;
            min-height: 2.5rem !important;
            margin: 0 !important;
        }
        body.sidebar-toggled .sidebar-wrapper .sidebar-brand-icon-fallback {
            font-size: 1rem !important;
        }
        body.sidebar-toggled .sidebar-wrapper .sidebar-brand-text {
            display: none !important;
        }
        /* Heading section (mis. "Master Data", "Laporan") juga bukan tombol,
           jadi sembunyikan di icon-rail supaya tidak ada label mengambang. */
        body.sidebar-toggled .sidebar .sidebar-heading,
        body.sidebar-toggled #accordionSidebar > .sidebar-heading {
            display: none !important;
        }

        /* Menu items: icon saja, sembunyikan label & arrow collapse */
        body.sidebar-toggled .sidebar .nav-item {
            margin: 0 .35rem;
        }
        body.sidebar-toggled .sidebar .nav-item .nav-link {
            padding: .65rem 0 !important;
            justify-content: center !important;
        }
        body.sidebar-toggled .sidebar .nav-item .nav-link i {
            margin-right: 0 !important;
            font-size: 1rem !important;
        }
        /* Sembunyikan total label menu di icon-rail (bukan cuma transparan).
           Label sub-menu di flyout tetap tampil karena flyout bukan .nav-link. */
        body.sidebar-toggled .sidebar .nav-item .nav-link span {
            display: none !important;
        }
        /* Tooltip native untuk icon-rail */
        body.sidebar-toggled .sidebar .nav-item .nav-link {
            position: relative;
        }
        body.sidebar-toggled .sidebar .nav-item .nav-link[data-toggle="collapse"]::after {
            display: none !important;
        }
        /* Saat menu punya child (collapse): tetap bisa di-hover untuk buka dropdown.
           Kita pakai pendekatan: hover pada li.nav-item tampilkan collapse child-nya. */
        body.sidebar-toggled .sidebar .nav-item .collapse {
            display: none; /* default sembunyi di icon-rail */
        }
        /* Specificity lebih tinggi dari .collapse.show Bootstrap */
        body.sidebar-toggled .sidebar .nav-item:hover > .collapse,
        body.sidebar-toggled .sidebar .nav-item .collapse.show,
        body.sidebar-toggled .sidebar .nav-item .collapse.show.collapse {
            display: block !important;
        }
        /* Posisikan sub-menu flyout di sebelah kanan icon rail */
        body.sidebar-toggled .sidebar .nav-item .collapse,
        body.sidebar-toggled .sidebar .nav-item .collapsing {
            position: absolute !important;
            left: 4.5rem !important;
            top: 0 !important;
            margin: 0 !important;
            min-width: 14rem;
            background-color: #ffffff;
            border-radius: .5rem;
            box-shadow: 0 .75rem 2rem rgba(15, 23, 42, .35),
                0 .25rem .5rem rgba(15, 23, 42, .15);
            padding: .35rem 0 !important;
            z-index: 1050;
        }
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner,
        body.sidebar-toggled .sidebar .nav-item .collapsing .collapse-inner {
            padding: .25rem 0 .35rem !important;
        }
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .collapse-item,
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .dropdown-item {
            margin: 2px .35rem !important;
        }
        /* Override warna teks di dalam flyout (light bg) supaya tidak abu-abu */
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .collapse-item,
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .dropdown-item {
            color: #4a5073 !important;
        }
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .collapse-item::before,
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .dropdown-item::before {
            background-color: #c5c9d6 !important;
        }
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .collapse-item:hover,
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .dropdown-item:hover {
            background-color: #f3f5fb !important;
            color: #2e3142 !important;
        }
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .collapse-item:hover::before,
        body.sidebar-toggled .sidebar .nav-item .collapse .collapse-inner .dropdown-item:hover::before {
            background-color: #4e73df !important;
        }
        /* Parent active tetap kelihatan */
        body.sidebar-toggled .sidebar .nav-item.active > .nav-link {
            box-shadow: 0 .25rem 1rem 0 rgba(78, 115, 223, .45);
        }
        /* Sembunyikan text di topbar user (suplai nama user) saat collapse */
        body.sidebar-toggled.topbar-user-hidden-helper .topbar .NamaUser {
            display: none !important;
        }
    }

    /* =========================================================
       MOBILE (≤768px): Sidebar jadi off-canvas slide-out
       Hidden by default, slide dari kiri saat .sidebar-mobile-open
       ========================================================= */
    @media (max-width: 768px) {
        /* Default mobile: sembunyikan sidebar wrapper */
        .sidebar-wrapper {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0;
            width: 16rem !important;
            max-width: 80vw !important;
            height: 100vh !important;
            max-height: 100vh !important;
            z-index: 1040;
            transform: translateX(-100%);
            box-shadow: none;
            border-right: 1px solid var(--sb-border);
        }

        /* Saat body punya .sidebar-mobile-open, slide masuk */
        body.sidebar-mobile-open .sidebar-wrapper {
            transform: translateX(0);
            box-shadow: 0 0 2rem rgba(0, 0, 0, .5);
        }

        /* Backdrop overlay saat sidebar mobile terbuka */
        .sidebar-mobile-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, .55);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 1035;
            opacity: 0;
            transition: opacity .25s ease-in-out;
        }
        body.sidebar-mobile-open .sidebar-mobile-backdrop {
            display: block;
            opacity: 1;
        }

        /* Brand lebih ringkas di mobile */
        .sidebar .sidebar-brand,
        .sidebar-wrapper > .sidebar-brand,
        a.sidebar-brand {
            min-height: 5rem !important;
            padding-top: .85rem !important;
            padding-bottom: .85rem !important;
        }
        .sidebar-wrapper .sidebar-brand .sidebar-brand-icon {
            width: 3rem !important;
            height: 3rem !important;
            min-width: 3rem !important;
            min-height: 3rem !important;
            margin: 0 0 .4rem 0 !important;
            box-shadow: 0 .25rem .75rem rgba(78, 115, 223, .45) !important;
        }
        .sidebar-wrapper .sidebar-brand .sidebar-brand-icon-fallback {
            font-size: 1.1rem !important;
        }
        .sidebar .sidebar-brand .sidebar-brand-text {
            font-size: .9rem !important;
        }

        /* Container-wrapper full width di mobile */
        #container-wrapper {
            padding-left: .85rem !important;
            padding-right: .85rem !important;
        }

        /* Navbar: rapikan padding & sembunyikan elemen yang tidak perlu */
        .navbar.topbar {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }
        #sidebarToggleTop {
            margin-right: .25rem !important;
            margin-left: 0 !important;
        }

        /* Footer mobile-friendly */
        footer.sticky-footer {
            padding: .75rem 0;
            text-align: center !important;
        }
        footer.sticky-footer .copyright {
            text-align: center !important;
            font-size: .8rem;
        }
        footer.sticky-footer .copyright span {
            display: inline !important;
        }

        /* Scroll-to-top button lebih kecil di mobile */
        a.scroll-to-top {
            width: 2.25rem !important;
            height: 2.25rem !important;
            line-height: 2.25rem !important;
            right: .85rem !important;
            bottom: 1rem !important;
        }
    }

    /* =========================================================
       Tablet kecil (769–992): tetap full sidebar
       ========================================================= */
    @media (min-width: 769px) and (max-width: 991.98px) {
        .sidebar-wrapper {
            width: 14rem !important;
            max-width: 14rem !important;
        }
        .sidebar-wrapper .sidebar-brand .sidebar-brand-icon {
            width: 4.5rem !important;
            height: 4.5rem !important;
            min-width: 4.5rem !important;
            min-height: 4.5rem !important;
        }
        .sidebar-wrapper .sidebar-brand .sidebar-brand-icon-fallback {
            font-size: 1.6rem !important;
        }
        .sidebar .sidebar-brand .sidebar-brand-text {
            font-size: .95rem !important;
        }
    }

    /* =========================================================
       Tombol toggle di navbar
       ========================================================= */
    #sidebarToggleTop {
        color: #ffffff;
        transition: background-color .15s ease-in-out;
    }
    #sidebarToggleTop:hover {
        background-color: rgba(255, 255, 255, .15) !important;
    }
    #sidebarToggleTop:active {
        background-color: rgba(255, 255, 255, .25) !important;
    }
    #sidebarToggleTop i {
        transition: transform .2s ease-in-out;
    }
    body.sidebar-toggled #sidebarToggleTop i.fa-bars {
        transform: rotate(90deg);
    }

    /* =========================================================
       Container fluid: rapikan padding default
       ========================================================= */
    @media (max-width: 768px) {
        #wrapper #content-wrapper #content {
            padding: 0;
        }
    }
</style>
<!-- Sidebar -->
{{--
    Fungsi menuIsActive() didefinisikan di:
    app/Helpers/sidebar_helper.php (auto-loaded via composer.json)

    Dipakai untuk menentukan apakah menu sidebar sedang aktif berdasarkan:
    - Exact match path
    - Prefix segment match (hanya jika menu adalah parent dari sub-route,
      bukan sibling group)
--}}
@if (auth()->user()->jabatan == 5)
    @php
        $logoUrl = Session::get('logo') ? '/storage/logo/' . Session::get('logo') : '';
        $initial = strtoupper(substr(Session::get('nama_usaha', 'P'), 0, 1));
    @endphp
    <div class="sidebar-wrapper">
        <a class="sidebar-brand d-flex flex-column align-items-center justify-content-center" href="/">
            <div class="sidebar-brand-icon">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" data-fallback="{{ $initial }}">
                @else
                    <span class="sidebar-brand-icon-fallback">{{ $initial }}</span>
                @endif
            </div>
            <div class="sidebar-brand-text mt-2"><b>{{ Session::get('nama_usaha') }}</b></div>
        </a>
        <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
            <li class="nav-item {{ Request::is('dashboard/usagesDashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="/dashboard/usagesDashboard/?cater_id={{ auth()->user()->id }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item {{ menuIsActive('/usages') && !Request::is('usages/sampah*') ? 'active' : '' }}">
                <a class="nav-link" href="/usages/?cater_id={{ auth()->user()->id }}">
                    <i class="fas fa-tint"></i>
                    <span>Pemakaian Air Bersih</span>
                </a>
            </li>
            <li class="nav-item {{ menuIsActive('/usages/sampah') ? 'active' : '' }}">
                <a class="nav-link" href="/usages/sampah/?cater_id={{ auth()->user()->id }}">
                    <i class="fas fa-street-view"></i>
                    <span>Retribusi Sampah</span>
                </a>
            </li>
        </ul>
    </div>
@else
    @php
        $logoUrl = Session::get('logo') ? '/storage/logo/' . Session::get('logo') : '';
        $initial = strtoupper(substr(Session::get('nama_usaha', 'P'), 0, 1));
    @endphp
<div class="sidebar-wrapper">
    <a class="sidebar-brand d-flex flex-column align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" data-fallback="{{ $initial }}">
            @else
                <span class="sidebar-brand-icon-fallback">{{ $initial }}</span>
            @endif
        </div>
        <div class="sidebar-brand-text mt-2"><b>{{ Session::get('nama_usaha') }}</b></div>
    </a>
    <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    @foreach (Session::get('menu') as $menu)
        @if ($menu->child->isEmpty())
            @php
                $isActive = menuIsActive($menu->link);
            @endphp
            {{-- Menu yang berfungsi sebagai section heading (icon='0' / kosong
                 di DB dan tidak punya child) dirender sbg label, bukan link. --}}
            @if (trim((string) $menu->icon) === '' || $menu->icon === '0')
                <div class="sidebar-heading">{{ $menu->title }}</div>
            @else
                <li class="nav-item {{ $isActive ? 'active' : '' }}">
                    <a class="nav-link" href="{{ $menu->link }}">
                        <i class="{{ $menu->icon }}"></i>
                        <span>{{ $menu->title }}</span>
                    </a>
                </li>
            @endif
        @else
            @php
                $hasActiveChild = $menu->child->contains(function ($child) {
                    return menuIsActive($child->link);
                });
            @endphp
            <li class="nav-item {{ $hasActiveChild ? 'active' : '' }}">
                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                    data-target="#collapse{{ $menu->id }}" aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}"
                    aria-controls="collapse{{ $menu->id }}">
                    <i class="{{ $menu->icon }}"></i>
                    <span>{{ $menu->title }}</span>
                </a>
                <div id="collapse{{ $menu->id }}" class="collapse {{ $hasActiveChild ? 'show' : '' }}" aria-labelledby="heading{{ $menu->id }}"
                    data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
                        @foreach ($menu->child as $child)
                            @php
                                $isChildActive = menuIsActive($child->link);
                            @endphp
                            <a class="collapse-item {{ $isChildActive ? 'active' : '' }}" href="{{ $child->link }}">{{ $child->title }}</a>
                        @endforeach
                    </div>
                </div>
            </li>
        @endif
    @endforeach
    </ul>
</div>
@endif
<!-- Sidebar -->
<script>
    // Fallback logo: jika gambar gagal load, ganti dengan inisial nama usaha
    document.addEventListener('DOMContentLoaded', function() {
        var imgs = document.querySelectorAll('.sidebar-brand-icon img[data-fallback]');
        imgs.forEach(function(img) {
            img.addEventListener('error', function() {
                var initial = this.getAttribute('data-fallback') || '?';
                var span = document.createElement('span');
                span.className = 'sidebar-brand-icon-fallback';
                span.textContent = initial;
                this.parentNode.replaceChild(span, this);
            });
        });
    });
</script>

<!-- Backdrop akan di-include terpisah di base.blade.php sebagai sibling #wrapper -->

<script>
    /**
     * Sidebar Toggle Logic
     * - Desktop (≥769px): toggle class .sidebar-toggled di <body> -> sidebar collapse jadi icon rail
     * - Mobile (≤768px): toggle class .sidebar-mobile-open di <body> -> sidebar slide dari kiri + backdrop
     */
    (function () {
        var MOBILE_BREAKPOINT = 768;
        var DESKTOP_COLLAPSED_KEY = 'sidebarDesktopCollapsed';

        function isMobile() {
            return window.innerWidth <= MOBILE_BREAKPOINT;
        }

        function closeAllCollapses() {
            // Tutup semua sub-menu terbuka (untuk konsistensi state)
            document.querySelectorAll('.sidebar .collapse.show').forEach(function (c) {
                try {
                    // Pakai Bootstrap jika tersedia
                    if (window.jQuery && typeof window.jQuery(c).collapse === 'function') {
                        window.jQuery(c).collapse('hide');
                    } else {
                        c.classList.remove('show');
                    }
                } catch (e) { /* noop */ }
            });
        }

        function applyDesktopState() {
            // Hapus state mobile jika ada
            document.body.classList.remove('sidebar-mobile-open');
            var backdrop = document.getElementById('sidebarMobileBackdrop');
            if (backdrop) backdrop.style.display = 'none';

            // Pulihkan state desktop collapse dari localStorage
            var collapsed = localStorage.getItem(DESKTOP_COLLAPSED_KEY) === '1';
            if (collapsed) {
                document.body.classList.add('sidebar-toggled');
            } else {
                document.body.classList.remove('sidebar-toggled');
            }
        }

        function clearDesktopState() {
            // Di mobile, hapus state icon-rail agar layout full
            document.body.classList.remove('sidebar-toggled');
        }

        function toggleSidebar() {
            if (isMobile()) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-toggled');
                // Simpan preferensi user
                var isCollapsed = document.body.classList.contains('sidebar-toggled');
                try {
                    localStorage.setItem(DESKTOP_COLLAPSED_KEY, isCollapsed ? '1' : '0');
                } catch (e) { /* localStorage mungkin diblokir */ }
                // Saat desktop collapse, pastikan semua sub-menu tertutup
                if (isCollapsed) {
                    closeAllCollapses();
                }
            }
        }

        function closeMobileSidebar() {
            document.body.classList.remove('sidebar-mobile-open');
        }

        // Inisialisasi saat DOM siap
        document.addEventListener('DOMContentLoaded', function () {
            // Terapkan state awal berdasarkan ukuran layar
            if (isMobile()) {
                clearDesktopState();
            } else {
                applyDesktopState();
            }

            // Bind tombol toggle (navbar)
            var toggleBtn = document.getElementById('sidebarToggleTop');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            // Bind klik di backdrop -> tutup sidebar mobile
            var backdrop = document.getElementById('sidebarMobileBackdrop');
            if (backdrop) {
                backdrop.addEventListener('click', function () {
                    closeMobileSidebar();
                });
            }

            // Saat user klik salah satu menu di sidebar (mobile), otomatis tutup
            document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (isMobile()) {
                        // Tunda sedikit agar navigasi tetap jalan
                        setTimeout(closeMobileSidebar, 50);
                    }
                });
            });

            // ESC key tutup sidebar di mobile
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) {
                    closeMobileSidebar();
                }
            });
        });

        // Sinkronkan state saat resize melintasi breakpoint
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (isMobile()) {
                    clearDesktopState();
                } else {
                    closeMobileSidebar();
                    applyDesktopState();
                }
            }, 100);
        });
    })();
</script>

<style>
    /* =========================================================
       Sidebar — Tata letak modern & clean
       ========================================================= */

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
        background-color: #ffffff;
        border-right: 1px solid #eef0f3;
    }

    /* Override width .sidebar (ul) yang dipaksa 18rem oleh custom.css */
    .sidebar-wrapper .sidebar {
        width: 100% !important;
        max-width: 100% !important;
    }

    .sidebar-wrapper > .sidebar-brand {
        flex-shrink: 0;
        position: relative;
        z-index: 3;
        background-color: #ffffff;
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

    /* Scrollbar tipis & halus */
    #accordionSidebar::-webkit-scrollbar {
        width: 6px;
    }
    #accordionSidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    #accordionSidebar::-webkit-scrollbar-thumb {
        background-color: #d6dae0;
        border-radius: 3px;
    }
    #accordionSidebar::-webkit-scrollbar-thumb:hover {
        background-color: #a8a8a8;
    }
    #accordionSidebar {
        scrollbar-width: thin;
        scrollbar-color: #d6dae0 transparent;
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
        border-bottom: 1px solid #eef0f3;
        text-align: center;
        gap: .5rem;
        box-sizing: border-box;
    }
    .sidebar .sidebar-brand .sidebar-brand-text {
        font-weight: 900 !important;
        font-size: 1.05rem !important;
        color: #1a1f2e !important;
        letter-spacing: .025em;
        line-height: 1.3;
        text-align: center;
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
        text-transform: none;
        font-style: normal;
    }

    /* Pengaman: pastikan teks di dalam <b> benar-benar bold + hitam */
    .sidebar .sidebar-brand .sidebar-brand-text,
    .sidebar .sidebar-brand .sidebar-brand-text b,
    .sidebar .sidebar-brand .sidebar-brand-text strong {
        color: #1a1f2e !important;
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
        color: #1a1f2e !important;
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
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        box-shadow: 0 .45rem 1.15rem rgba(78, 115, 223, .55);
        position: relative;
        text-decoration: none !important;
        color: #ffffff;
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
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); /* warna di belakang image */
    }

    /* Inisial nama usaha (fallback jika logo gagal load) */
    .sidebar-wrapper .sidebar-brand .sidebar-brand-icon-fallback {
        color: #ffffff;
        font-weight: 800;
        font-size: 2.4rem;
        letter-spacing: .02em;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
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
        color: #4a5073;
        transition: all .2s ease-in-out;
        display: flex !important;
        align-items: center;
        text-align: left;
    }
    .sidebar .nav-item .nav-link i {
        font-size: .9rem !important;
        margin-right: .75rem !important;
        width: 1.25rem;
        text-align: center;
        color: #8a8fa3;
        transition: color .2s ease-in-out;
    }
    .sidebar .nav-item .nav-link span {
        font-size: .875rem;
        display: inline !important;
        flex: 1;
    }
    .sidebar .nav-item .nav-link:hover {
        background-color: #f3f5fb !important;
        color: #2e3142 !important;
    }
    .sidebar .nav-item .nav-link:hover i {
        color: #4e73df;
    }

    /* ---------- Divider (tidak dipakai antar menu, tapi tetap tersedia) ---------- */
    .sidebar hr.sidebar-divider {
        margin: .85rem 1.1rem;
        border-top: 1px solid #eef0f3;
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
        color: #9aa0b4;
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
        color: #5a607a;
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
        background-color: #c5c9d6;
        transition: all .2s ease-in-out;
        flex-shrink: 0;
    }

    .sidebar .nav-item .collapse .collapse-inner .collapse-item:hover,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item:hover,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item:hover,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item:hover {
        background-color: #f3f5fb !important;
        color: #2e3142 !important;
    }
    .sidebar .nav-item .collapse .collapse-inner .collapse-item:hover::before,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item:hover::before,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item:hover::before,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item:hover::before {
        background-color: #4e73df;
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
        color: #ffffff !important;
        background-color: #4e73df !important;
        font-weight: 600 !important;
        box-shadow: 0 .25rem 1rem 0 rgba(78, 115, 223, .35);
        border-radius: .5rem !important;
    }
    .sidebar .nav-item.active .nav-link i,
    .sidebar .nav-item.active > .nav-link i {
        color: #ffffff !important;
    }
    .sidebar .nav-item.active .nav-link:hover,
    .sidebar .nav-item.active > .nav-link:hover {
        background-color: #4262c5 !important;
    }

    /* Submenu aktif — specificity disejajarkan dengan template SB Admin 2 */
    .sidebar .nav-item .collapse .collapse-inner .collapse-item.active,
    .sidebar .nav-item .collapse .collapse-inner .dropdown-item.active,
    .sidebar .nav-item .collapsing .collapse-inner .collapse-item.active,
    .sidebar .nav-item .collapsing .collapse-inner .dropdown-item.active {
        color: #ffffff !important;
        background-color: #4e73df !important;
        font-weight: 600 !important;
        box-shadow: 0 .15rem .6rem 0 rgba(78, 115, 223, .3);
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
        color: #4e73df !important;
        background-color: #eef1fb !important;
        font-weight: 600 !important;
        border-radius: .5rem !important;
    }
    .sidebar .nav-item .nav-link[aria-expanded="true"] i {
        color: #4e73df !important;
    }
    .sidebar .nav-item .nav-link[aria-expanded="true"]:hover {
        background-color: #e4e9f7 !important;
    }

    /* Hapus <br> bawaan blade */
    .sidebar > br {
        display: none;
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
            <div class="sidebar-brand-text mt-2" style="font-weight: 900 !important; font-size: 1.05rem !important; color: #1a1f2e;"><b style="font-weight: 900 !important; color: #1a1f2e !important;">{{ Session::get('nama_usaha') }}</b></div>
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
        <div class="sidebar-brand-text mt-2" style="font-weight: 900 !important; font-size: 1.05rem !important; color: #1a1f2e;"><b style="font-weight: 900 !important; color: #1a1f2e !important;">{{ Session::get('nama_usaha') }}</b></div>
    </a>
    <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    @foreach (Session::get('menu') as $menu)
        @if ($menu->child->isEmpty())
            @php
                $isActive = menuIsActive($menu->link);
            @endphp
            <li class="nav-item {{ $isActive ? 'active' : '' }}">
                <a class="nav-link" href="{{ $menu->link }}">
                    <i class="{{ $menu->icon }}"></i>
                    <span>{{ $menu->title }}</span>
                </a>
            </li>
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

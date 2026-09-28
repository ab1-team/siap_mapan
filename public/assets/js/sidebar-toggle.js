/**
 * Sidebar Toggle Logic
 * - Desktop (≥769px): toggle class .sidebar-toggled di <body> -> sidebar collapse jadi icon rail
 * - Mobile (≤768px): toggle class .sidebar-mobile-open di <body> -> sidebar slide dari kiri + backdrop
 *
 * Catatan:
 * - Aman dipakai di banyak halaman (hanya mount jika elemen yang dibutuhkan ada).
 * - Memakai vanilla JS (tidak butuh jQuery/Bootstrap JS) sehingga tidak konflik
 *   dengan handler Bootstrap 4/5 yang mungkin double-load.
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
        var collapsed = false;
        try {
            collapsed = localStorage.getItem(DESKTOP_COLLAPSED_KEY) === '1';
        } catch (e) { /* localStorage mungkin diblokir */ }

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

    function mount() {
        // Terapkan state awal berdasarkan ukuran layar
        if (isMobile()) {
            clearDesktopState();
        } else {
            applyDesktopState();
        }

        // Bind tombol toggle (navbar)
        var toggleBtn = document.getElementById('sidebarToggleTop');
        if (toggleBtn) {
            // Pakai event listener yang di-attach sekali; dengan { once: false }
            // (default) akan tetap mendengarkan klik berikutnya.
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
    }

    function bindResize() {
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
    }

    // Inisialisasi saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            mount();
            bindResize();
        });
    } else {
        mount();
        bindResize();
    }
})();
<?php

/**
 * Helper untuk menentukan apakah sebuah menu/link sidebar sedang aktif.
 *
 * Strategi pencocokan:
 * - Special case untuk menu Dashboard (link '/'):
 *     Aktif saat URL = '/' (home) atau apapun di bawah '/dashboard/*'.
 *     Ini mengakomodasi route '/' yang mengarah ke DashboardController@index
 *     (home) dan beberapa sub-page dashboard (mis. '/dashboard/installations').
 * - Exact match → selalu dicek
 * - Prefix segment match → hanya jika TIDAK ada sibling menu lain
 *   yang path-nya merupakan child dari path ini.
 *
 * Contoh kasus 'pengaturan':
 *   Sibling: /pengaturan/coa, /pengaturan/sop, /pengaturan/rekening
 *   -> ada sibling dengan prefix 'pengaturan/' -> prefix match DITOLAK
 *   -> /pengaturan hanya aktif di '/pengaturan' exact
 *
 * Contoh kasus 'dashboard':
 *   Tidak ada sibling dengan prefix 'dashboard/'
 *   -> prefix match DITERIMA
 *   -> /dashboard aktif di '/dashboard', '/dashboard/installations', dll.
 *
 * Segment-based prefix (path + '/') supaya '/tagihan' TIDAK match '/tagihan-bulanan'
 * dan '/dashboard' TIDAK match '/dashboardinstallations'.
 */
if (! function_exists('menuIsActive')) {
    function menuIsActive($link)
    {
        $currentPath = trim((string) request()->path(), '/');

        $path = trim(parse_url((string) $link, PHP_URL_PATH) ?? '', '/');

        // 0) Special case: menu Dashboard (link = '/').
        //    Karena '/' sebagai root tidak punya segment, logika segment-prefix
        //    di bawah tidak bisa diterapkan. Kita tangani secara eksplisit:
        //    - root ('/') aktif saat URL persis '/' (home).
        //    - 'dashboard' segment aktif saat URL apa pun di bawah '/dashboard/*'.
        if ($path === '' || $path === '/') {
            if ($currentPath === '') {
                return true; // URL persis '/'
            }
            // Atau ketika berada di sub-page dashboard, mis. '/dashboard/installations'
            if ($currentPath === 'dashboard' || str_starts_with($currentPath, 'dashboard/')) {
                return true;
            }
            return false;
        }

        if ($currentPath === '') {
            // URL di root '/', tapi menu ini BUKAN menu Dashboard -> tidak aktif.
            return false;
        }

        // 1) Exact match selalu aktif
        if ($currentPath === $path) return true;

        // 2) Prefix segment match — hanya jika TIDAK ada sibling menu
        //    lain yang path-nya juga berada di bawah path ini.
        //    Ambil semua menu (beserta child-nya) dari session untuk dicek.
        $allMenus = \Illuminate\Support\Facades\Session::get('menu', collect());

        // Flatten: ambil semua link menu + child
        $allLinks = [];
        foreach ($allMenus as $m) {
            $allLinks[] = (string) $m->link;
            if (isset($m->child) && $m->child->isNotEmpty()) {
                foreach ($m->child as $c) {
                    $allLinks[] = (string) $c->link;
                }
            }
        }

        $hasSiblingUnderPath = false;
        foreach ($allLinks as $otherLink) {
            $otherPath = trim(parse_url($otherLink, PHP_URL_PATH) ?? '', '/');
            if ($otherPath === '' || $otherPath === $path) continue;

            if (str_starts_with($otherPath, $path . '/')) {
                $hasSiblingUnderPath = true;
                break;
            }
        }

        if (! $hasSiblingUnderPath) {
            return str_starts_with($currentPath, $path . '/');
        }

        return false;
    }
}

<?php

/**
 * Helper untuk menentukan apakah sebuah menu/link sidebar sedang aktif.
 *
 * Strategi pencocokan:
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
        $currentPath = trim(request()->path(), '/');
        if ($currentPath === '') return false;

        $path = trim(parse_url((string) $link, PHP_URL_PATH) ?? '', '/');
        if ($path === '') return false;

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

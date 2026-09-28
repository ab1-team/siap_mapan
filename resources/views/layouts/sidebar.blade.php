{{--
    CSS sidebar dipindahkan ke public/assets/css/sidebar-custom.css
    dan di-include di layouts/base.blade.php <head>.
    Alasan: style di tengah body rentan gagal di-parse browser, dan
    menyulitkan caching browser.

    Handler toggle sidebar dipindahkan ke public/assets/js/sidebar-toggle.js
    dan di-include di layouts/base.blade.php sebelum ruang-admin.js.
--}}

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
{{--
    Catatan:
    - CSS sidebar ada di public/assets/css/sidebar-custom.css (dimuat oleh base.blade.php).
    - JS toggle sidebar ada di public/assets/js/sidebar-toggle.js (dimuat oleh base.blade.php).
    - Inline kecil ini hanya untuk fallback logo (tidak terkait toggle).
--}}
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
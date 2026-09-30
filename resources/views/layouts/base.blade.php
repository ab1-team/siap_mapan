<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <meta name="description" content="">
    <meta name="author" content="">

    {{-- BUG #5 FIX: Tambah fallback href jika session icon null agar tidak
         muncul error 404 di console. Pakai favicon.ico yang sudah ada di
         folder public/. --}}
    <link rel="apple-touch-icon" sizes="76x76"
        href="{{ Session::get('icon') ?: asset('favicon.ico') }}">
    <link rel="icon" type="image/png"
        href="{{ Session::get('icon') ?: asset('favicon.ico') }}">

    <title>
        {{ $title ?? 'x' }} &mdash; PAMSIDES
    </title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Styling tambahan untuk SweetAlert Logout */
        .swal-logout-popup .swal2-title {
            font-size: 1.5rem !important;
            color: #e74a3b;
        }
        .swal-logout-popup .swal2-html-container {
            margin-top: 1rem !important;
        }
        .swal-logout-popup {
            border-radius: 12px !important;
            padding: 1.5rem !important;
        }
        .swal2-popup.swal-logout-popup {
            box-shadow: 0 10px 40px rgba(231, 74, 59, 0.25) !important;
        }
        .swal-logout-popup .btn {
            margin: 0 4px !important;
            transition: all 0.2s ease;
        }
        .swal-logout-popup .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <link href="/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/ruang-admin-min.css" rel="stylesheet">
    {{-- Custom sidebar styles (dipindahkan dari inline styles di sidebar.blade.php) --}}
    {{-- BUG #2 FIX: cache-busting diganti dari time() ke versi statis agar
         CDN/proxy bisa cache dan path tidak ke-drop oleh URL rewrite server. --}}
    <link href="{{ asset('assets/css/sidebar-custom.css') }}?v=1.0.1" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />

    <link href="/assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- BUG #2 FIX: cache-busting distandarkan ke versi statis --}}
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v=1.0.1">

    <style>
        .form-control,
        .js-select-2 {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
        }

        .camera-container {
            position: relative;
            text-align: center;
            width: 100%;
            height: 100%;
        }

        .camera-container video {
            width: 100%;
            height: 100%;
            max-height: 200px;
            display: block;
            object-fit: cover;
        }

        .camera-container video.mirror {
            transform: scaleX(-1);
        }

        .scan-overlay {
            position: absolute;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2;
        }

        .scan-overlay.top {
            top: 0;
            left: 20%;
            width: 60%;
            height: 40%;
        }

        .scan-overlay.bottom {
            bottom: 0;
            left: 20%;
            width: 60%;
            height: 40%;
        }

        .scan-overlay.left {
            top: 0%;
            left: 0;
            width: 20%;
            height: 100%;
        }

        .scan-overlay.right {
            top: 0%;
            right: 0;
            width: 20%;
            height: 100%;
        }

        .scan-area {
            position: absolute;
            top: 40%;
            left: 20%;
            width: 60%;
            height: 20%;
            border: 3px solid #fff;
            box-sizing: border-box;
            z-index: 3;
        }

        .qr-wrapper {
            width: 80px;
            height: 80px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrapper img,
        .qr-wrapper svg {
            width: 100% !important;
            height: 100% !important;
        }
    </style>

    @yield('style')
</head>

<body id="page-top">
    <!-- Backdrop untuk sidebar mobile (sibling #wrapper, agar fixed-position
         benar-benar relatif ke viewport meski parent ada transform) -->
    <div class="sidebar-mobile-backdrop" id="sidebarMobileBackdrop" aria-hidden="true"></div>

    <div id="wrapper">

        @include('layouts.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('layouts.navbar')

                <div class="container-fluid" id="container-wrapper">
                    @yield('content')
                </div>
            </div>
            @if (!request()->is('transactions/tagihan_bulanan'))
                @include('layouts.footer')
            @endif

        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    @yield('modal')

    <form action="/logout" method="post" id="logoutForm">
        @csrf
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/vendor/jquery/jquery.min.js"></script>
    <script src="/assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="/assets/vendor/chart.js/Chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script src="/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.jquery.min.js"></script>
    {{-- BUG #1 FIX: Bootstrap 5 CDN dihapus. SB Admin 2 (Ruang Admin) pakai Bootstrap 4.
         Memuat BS5 setelah BS4 menyebabkan konflik behavior collapse/transisi yang
         membuat layout sidebar icon-rail tidak konsisten antara lokal dan online. --}}
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="/assets/js/demo/ruang-admin.js"></script>
    {{-- Sidebar toggle handler (vanilla JS, dipasang setelah ruang-admin agar konsisten) --}}
    {{-- BUG #2 FIX: cache-busting diganti dari time() ke versi statis --}}
    <script src="{{ asset('assets/js/sidebar-toggle.js') }}?v=1.0.1"></script>
    {{-- Logout --}}
    <script>
        $(document).on('click', '#logoutButton', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '<i class="fas fa-sign-out-alt text-danger"></i> Konfirmasi Logout',
                html: `
                    <div class="text-center mb-2">
                        <span class="badge badge-danger p-2" style="font-size: 14px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Anda akan keluar dari sesi ini
                        </span>
                    </div>
                    <p class="mt-3 mb-1 text-dark">Apakah Anda yakin ingin <b>logout</b> sekarang?</p>
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Pastikan semua pekerjaan sudah tersimpan sebelum keluar.</small>
                `,
                icon: 'warning',
                iconColor: '#e74a3b',
                showCancelButton: false,
                showDenyButton: true,
                confirmButtonText: '<i class="fas fa-sign-out-alt mr-1"></i> Logout',
                denyButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                showCloseButton: false,
                focusConfirm: false,
                confirmButtonColor: '#e74a3b',
                denyButtonColor: '#858796',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger btn-sm px-3',
                    denyButton: 'btn btn-secondary btn-sm px-3',
                    popup: 'swal-logout-popup'
                },
                buttonsStyling: false,
                backdrop: 'rgba(78, 115, 223, 0.4)',
                timer: 15000,
                timerProgressBar: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading sebelum submit
                    Swal.fire({
                        title: 'Logging out...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $('#logoutForm').submit();
                }
            });
        })
    </script>

    <script>
        //property lainya
        function open_window(link) {
            return window.open(link)
        }

        $(document).on('click', '.btn-modal-close', function(e) {
            e.preventDefault();
            $('.modal').modal('hide');
        });

        const formatDate = (dateString) => {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            return `${day}/${month}/${year}`;
        };

        var toastMixin = Swal.mixin({
            toast: true,
            icon: 'success',
            position: 'top-right',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    </script>

    {{-- BUG #7 FIX: Pakai Session::has() daripada Session::get() agar null
         atau string kosong tidak masuk ke blok ini. --}}
    @if (Session::has('success') && Session::get('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil',
                text: '{{ Session::get('success') }}.',
            }).then((result) => {
                window.open('/dataset/{{ time() }}')
            })
        </script>
    @endif

    @yield('script')

</body>

</html>

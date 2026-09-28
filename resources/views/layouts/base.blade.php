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

    <link rel="apple-touch-icon" sizes="76x76" href="{{ Session::get('icon') }}">
    <link rel="icon" type="image/png" href="{{ Session::get('icon') }}">

    <title>
        {{ $title ?? 'x' }} &mdash; PAMSIDES
    </title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <link href="/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/ruang-admin-min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />

    <link href="/assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="/assets/css/custom.css?v={{ time() }}">

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="/assets/js/demo/ruang-admin.js"></script>
    {{-- Logout --}}
    <script>
        $(document).on('click', '#logoutButton', function(e) {
            e.preventDefault();

            Swal.fire({
                title: "Konfirmasi Logout",
                icon: 'info',
                showDenyButton: true,
                confirmButtonText: "Logout",
                denyButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logoutForm').submit();
                }
            });
        })
    </script>

    <script>
        var numFormat = new Intl.NumberFormat('en-EN', {
            minimumFractionDigits: 2
        })

        //cari customors
        $('#PelunasanInstalasi').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'states',
            source: function(query, process) {
                if (query.length < 2) return;

                $.ajax({
                    url: '/installations/CariPelunasan_Instalasi',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(result) {
                        var states = [];
                        result.map(function(item) {
                            if (item.installation.length > 0) {
                                item.installation.map(function(instal) {
                                    states.push({
                                        id: instal.id,
                                        installation: instal,
                                        name: item.nama +
                                            ' - ' + instal.village.nama +
                                            ' - ' + instal.kode_instalasi +
                                            ' [' + item.nik + ']',
                                        value: instal.id
                                    })
                                })
                            }
                        });

                        process(states);
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat pemanggilan custommers:", error);
                        process([]);
                    }
                });
            },

            displayKey: 'name',
            autoSelect: true,
            fitToElement: true,
            items: 10

        }).bind('typeahead:selected', function(event, item) {
            var installation = item.installation
            var trx = installation.transaction

            var sum_total = 0;
            var rekening_debit = 0;
            var rekening_kredit = 0;

            trx.map(function(item) {
                rekening_debit = item.rekening_debit;
                rekening_kredit = item.rekening_kredit;
                sum_total += item.total;
            })

            var rek_debit = rekening_debit;
            var rek_kredit = rekening_kredit;
            var tagihan = (installation.biaya_instalasi);
            console.log(sum_total);

            $("#installation").val(installation.id);
            $("#order").val(installation.order);
            $("#kode_instalasi").val(installation.kode_instalasi);
            $("#alamat").val(installation.village.nama);
            $("#package").val(installation.package.kelas);
            $("#abodemen").val(numFormat.format(installation.abodemen));
            $("#biaya_sudah_dibayar").val(numFormat.format(sum_total));
            $("#tagihan").val(numFormat.format(tagihan));
            $("#pembayaran").val(numFormat.format(tagihan));
            $("#_total").val(numFormat.format(sum_total));
            $("#rek_debit").val(rek_debit);
            $("#rek_kredit").val(rek_kredit);

        });
        //end cari customors
    </script>

    <script>
        var numFormat = new Intl.NumberFormat('en-EN', {
            minimumFractionDigits: 2
        })

        var dataCustomer;

        //Tagihan Bulanan (Aktif)
        $('#TagihanBulanan').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'states',
            source: function(query, process) {
                if (query.length < 2) return;

                $.ajax({
                    url: '/installations/CariTagihan_bulanan',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(result) {
                        var states = [];
                        result.map(function(item) {
                                let inisial = item.package_inisial ? '-' + item.package_inisial : '';
                                let kategori = item.kategori == 1 ? 'Air' : 'Sampah';
                                states.push({
                                    kode_instalasi: item.kode_instalasi,
                                    name: item.nama + ' - ' + item.kode_instalasi +
                                        inisial + ' [' + item.nik + '] ' + '- ( ' + kategori + ' )',
                                    value: item.kode_instalasi,
                                    item: item,
                                });
                            });
                        process(states);
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat pemanggilan custommers:", error);
                        process([]);
                    }
                });
            },

            displayKey: 'name',
            autoSelect: true,
            fitToElement: true,
            items: 10

        }).bind('typeahead:selected', function(event, item) {
            formTagihanBulanan(item.item);
        });

        function formTagihanBulanan(installation) {
            $.get('/installations/usage/' + installation.kode_instalasi, (result) => {
                if (result.success) {
                    $('#accordion').html(result.view)
                } else {
                    $('#accordion').html(result.view)
                }

                dataCustomer = {
                    item: installation,
                    rek_debit: result.rek_debit,
                    rek_kredit: result.rek_kredit,
                }
            })
        }
        //end cari Tagihan perbulan
    </script>

    <script>
        // Awal script untuk cari Anggota Pemakaian
        $('#carianggota').typeahead({
            hint: true,
            highlight: true,
            minLength: 2 // Minimal karakter untuk memulai pencarian
        }, {
            name: 'states',
            source: function(query, process) {
                if (query.length < 2) return; // Hindari pengiriman permintaan jika terlalu pendek

                $.ajax({
                    url: '/usages/cari_anggota',
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(result) {
                        var states = result.map(function(item) {
                            return {
                                id: item.customer.kode_instalasi,
                                name: `${item.customer.nama} [${item.customer.kode_instalasi}]`,
                                value: item.customer.kode_instalasi,
                                data: item
                            };
                        });

                        process(states);
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat memanggil pelanggan:", error);
                        process([]); // Tetap proses dengan data kosong jika ada error
                    }
                });
            },
            displayKey: 'name',
            autoSelect: true,
            fitToElement: true,
            items: 10
        }).bind('typeahead:selected', function(event, item) {
            var data = item.data;
            var usage = data.usage;

            // Default nilai awal adalah 0 jika tidak ada data penggunaan sebelumnya
            var nilai_awal = usage ? usage.akhir || 0 : 0;

            // Set nilai awal dan customer_id di form
            $('#awal').val(nilai_awal);
            $('#customer_id').val(data.customer.customer_id);
            $('#id_instalasi').val(data.customer.id);
        });

        $(document).on('change', '.hitungan', function() {
            var awal = parseFloat($('#awal').val()) || 0;
            var akhir = parseFloat($('#akhir').val()) || 0;
            var jarak_awal = parseFloat($('#jarak_awal').val()) || 0;

            if (akhir <= awal || akhir === 0) {
                // Menggunakan SweetAlert2 untuk menampilkan pesan kesalahan
                Swal.fire({
                    icon: 'error',
                    title: 'Nilai akhir tidak valid',
                    text: 'Nilai akhir harus lebih besar dari nilai awal.',
                    confirmButtonText: 'Coba lagi'
                });
                $('#jumlah').val('');
                return;
            }

            var selisih = akhir - awal;

            if (selisih >= jarak_awal) {
                $('#jumlah').val(selisih);
                $('#awal').val(awal);
            } else {
                $('#jumlah').val();
                alert('Selisih tidak memenuhi syarat jarak minimum.');
            }
        });
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

    @if (Session::get('success'))
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

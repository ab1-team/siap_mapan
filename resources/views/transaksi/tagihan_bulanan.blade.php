@extends('layouts.base')

@section('content')
    <style>
        .search-bar-sticky {
            position: sticky;
            top: 75px;
            z-index: 1050;
            background-color: #f8f9fc;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 0.5rem 0.75rem;
        }

        .sidebar-pelanggan-sticky {
            position: sticky;
            top: calc(90px + 0.5rem + 2.5rem);
            z-index: 1040;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 576px) {
            .search-bar-sticky {
                top: 56px;
                padding: 0.5rem;
                border-radius: 0.5rem;
            }

            .sidebar-pelanggan-sticky {
                top: calc(56px + 0.5rem + 1rem);
            }
        }

        @media (max-width: 767.98px) {
            .sidebar-pelanggan-sticky {
                position: static;
                top: auto;
                box-shadow: none;
            }
        }
    </style>

    <div class="container-fluid" id="container-wrapper">
        <div class="search-bar-sticky">
            <div class="row align-items-center g-2">
                <div class="col-9">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control is-valid" style="height: 38px; border-radius: 0.5rem;"
                            id="TagihanBulanan" placeholder="Usages (Kode Installasi / Nama Custommers)">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group mb-0">
                        <a href="#" class="btn btn-warning w-100" style="height: 38px; border-radius: 0.5rem;"
                            type="button" id="BtndetailTransaksi">
                            <span class="text">Detail Transaksi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-2 mb-2 bg-white">
        <div id="accordion">
            <div class="text-center">
                <img src="../../assets/img/air.png" style="max-height: 200px;" class="mb-3">
                <h3 class="text-gray-800 font-weight-bold">Tagihan!</h3>
                <p class="lead text-gray-800 mx-auto">Search untuk melakukan pembayaran</p>
                <a href="/">&larr; Back to Dashboard</a>
            </div>
        </div>
    </div>
    <form action="/transactions/hapus" method="post" id="formHapus">
        @csrf

        <input type="hidden" name="del_id" id="del_id">
        <input type="hidden" name="del_istal_id" id="del_istal_id">
    </form>
@endsection
@section('modal')
    <div class="modal fade" id="detailTransaksi" tabindex="-1" role="dialog" aria-labelledby="detailTransaksiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable" style="max-width: 100%; margin: 0; height: 100%;"
            role="document">
            <div class="modal-content" style="height: 100%; border-radius: 0;">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailTransaksiLabel">Detail Transaksi Tagihan</h5>
                </div>
                <div class="modal-body" style="overflow-y: auto;">
                    <div id="LayoutdetailTransaksi"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    {{-- BUG #3 FIX: Typeahead scripts (termasuk #TagihanBulanan handler dan
         fungsi formTagihanBulanan) dipindahkan dari base ke partial ini.
         Hanya di-include di halaman yang punya element target. --}}
    @include('layouts.partials.typeahead-scripts')

    <script>
        //hitung total (tagihan bulanan)
        $(document).on('change', '#pembayaran', function() {
            function cleanNumber(value) {
                let cleanNumber = value.toString().replace(/,/g, '');
                return parseFloat(cleanNumber);
            }
            var jumlah = cleanNumber($(this).val());
            var jumlah_bayar = cleanNumber($("#tagihan").val());
            var total = (jumlah - jumlah_bayar);

            $("#total").val(numFormat.format(Math.abs(total)));
        });

        //detail transaksi
        $(document).on('click', '#BtndetailTransaksi', function(e) {
            var id = dataCustomer.item.id;
            var rek_debit = dataCustomer.rek_debit;
            var rek_kredit = dataCustomer.rek_kredit;

            if (id != '') {
                $.ajax({
                    url: '/transactions/detail_transaksi_tagihan',
                    type: 'get',
                    data: {
                        id,
                        rek_debit,
                        rek_kredit
                    },
                    success: function(result) {
                        $('#detailTransaksi').modal('show')

                        $('#detailTransaksiLabel').html(result.label)
                        $('#LayoutdetailTransaksi').html(result.view)
                    }
                })
            }
        })

        //hapus detail transaksi
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault()

            var id = $(this).attr('data-id')

            $.get('/transactions/data/' + id, function(result) {

                $('#del_id').val(result.id)
                $('#del_instal_id').val(result.installation_id)
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Setelah menekan tombol Hapus Transaksi dibawah, maka transaksi ini akan dihapus dari aplikasi secara permanen. Dan mengubah status (UNPAID) di pemakaian',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus Transaksi',
                    cancelButtonText: 'Batal',
                    icon: 'warning'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = $('#formHapus')
                        $.ajax({
                            type: form.attr('method'),
                            url: form.attr('action'),
                            data: form.serialize(),
                            success: function(result) {
                                if (result.success) {
                                    Swal.fire('Berhasil!', result.msg, 'success')
                                        .then(() => {
                                            window.location.href =
                                                '/transactions/tagihan_bulanan';
                                        });
                                }

                            }
                        })
                    }
                })
            })
        })

        //simpan data pembayaran tagihan bulanan
        $(document).on('click', '.SimpanTagihan', function(e) {
            e.preventDefault();
            $('small').html('');

            var target = $(this).attr('data-form'); // narget button
            var form = $(target); // narget form = id
            var actionUrl = form.attr('action');

            $.ajax({
                type: 'POST',
                url: actionUrl,
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Swal.fire({
                            title: result.msg,
                            icon: "success",
                            draggable: true
                        });

                        formTagihanBulanan(result.installation)
                        window.open('/transactions/dokumen/struk_tagihan/' + result.transaksi)
                    }
                },

                error: function(result) {
                    const response = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');

                    if (response && typeof response === 'object') {
                        $.each(response, function(key, message) {
                            $('#' + key)
                                .closest('.input-group.input-group-static')
                                .addClass('is-invalid');

                            $('#msg_' + key).html(message);
                        });
                    }
                }
            });
        });

        //simpan pembayaran installations
        $(document).on('click', '#simpanpembayaran', function(e) {
            e.preventDefault();
            $('small').html('');

            var form = $('#FormPembayaran');
            var actionUrl = form.attr('action');

            $.ajax({
                type: 'POST',
                url: actionUrl,
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Swal.fire({
                            title: result.msg,
                            text: "Lanjut Pembayaran Tagihan?",
                            icon: "success",
                            showDenyButton: true,
                            confirmButtonText: "Lanjutkan",
                            denyButtonText: `Tidak`
                        }).then((res) => {
                            if (res.isConfirmed) {
                                window.location.reload();
                            } else if (res.isDenied) {
                                window.location.href = '#';
                            }
                        });

                        window.open('/transactions/dokumen/struk_instalasi/' + result.transaction_id)
                    }
                },
                error: function(result) {
                    const response = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');

                    if (response && typeof response === 'object') {
                        $.each(response, function(key, message) {
                            $('#' + key)
                                .closest('.input-group.input-group-static')
                                .addClass('is-invalid');

                            $('#msg_' + key).html(message);
                        });
                    }
                }
            });
        });

        function cleanNumber(value) {
            let cleanNumber = value.toString().replace(/,/g, '');
            return parseFloat(cleanNumber);
        }

        //form input denda
        // $(document).on('change', '.tgl_transaksi', function() {
        //     var tglTransaksi = $(this).val();

        //     var id = $(this).attr('data-id');
        //     var tglAkhir = new Date($('#tgl-akhir-' + id).val()).getTime();
        //     var denda = $('#denda-' + id).val();

        //     let tgl = tglTransaksi.split('/');
        //     let hari = tgl[0];
        //     let bulan = tgl[1];
        //     let tahun = tgl[2];

        //     var tanggal = new Date(`${tahun}-${bulan}-${hari}`).getTime();
        //     if (tanggal >= tglAkhir) {
        //         denda = denda;
        //     } else {
        //         denda = 0;
        //     }

        //     var denda_tagihan = numFormat.format(Math.abs(denda))
        //     var abodemen = cleanNumber($("#abodemen-bulanan-" + id).val());
        //     var tagihan = cleanNumber($("#tagihan-" + id).val());
        //     var denda = cleanNumber(denda_tagihan);

        //     $("#denda-bulanan-" + id).val(denda_tagihan);
        //     $('#pembayaran-' + id).val(numFormat.format(Math.abs(denda + abodemen + tagihan)))
        // })

        $(document).on('change', '.perhitungan', function() {
            var jumlah = cleanNumber($(this).val());

            var id = $(this).attr('data-id');
            var tagihan = cleanNumber($("#tagihan-" + id).val());
            var pembayaran = cleanNumber($("#pembayaran-" + id).val());

            if (pembayaran > tagihan) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran tidak valid',
                    text: 'Pembayaran tidak boleh melebihi tagihan.',
                    confirmButtonText: 'Coba lagi'
                });
                $('#pembayaran-' + id).val('');
                return;
            }
        });
    </script>
@endsection

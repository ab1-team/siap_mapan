@extends('layouts.base')
@php
    $ketik_search = 'PelunasanInstalasi';
    $label_search = 'Installations (Kode Installasi / Nama Custommers)';
@endphp
@section('content')
    <div class="container-fluid" id="container-wrapper">
        <form action="/transactions" method="post" id="FormPembayaran">
            @csrf
            <input type="hidden" name="clay" id="clay" value="pelunasaninstalasi">
            <input type="hidden" name="istallation_id" id="installation">
            <input type="hidden" id="rek_debit">
            <input type="hidden" id="rek_kredit">

            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <!-- Bagian Informasi Customer -->
                            <div class="alert alert-info d-flex align-items-center m-0 text-white" role="alert"
                                style="border-radius: 1;">
                                <!-- Gambar -->
                                <img src="../../assets/img/pls.png"
                                    style="max-height: 160px; margin-right: 15px; margin-left: 10px;"
                                    class="img-fluid  d-none d-lg-block">

                                <!-- Konten Teks -->
                                <div class="flex-grow-1">
                                    <div class="form-group">
                                        <input type="text" class="form-control is-valid" id="{{ $ketik_search }}"
                                            placeholder="{{ $label_search }}">
                                        <div class="valid-feedback" text-white>
                                            Masukkan (Kode Installasi / Nama Custommers)
                                        </div>
                                    </div>
                                    <hr class="my-2 bg-white">
                                    <div class="mt-3">
                                        <table class="table table-bordered table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th colspan="4" class="text-black">Detail Instalasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 50%; font-size: 14px; padding: 8px;"
                                                        class="text-black">
                                                        Tgl Order
                                                        <input
                                                            style="float: right; width: 40%; padding: 1px; text-align: center;"
                                                            id="order" disabled>
                                                    </td>
                                                    <td style="width: 50%; font-size: 14px; padding: 8px;"
                                                        class="text-black">
                                                        Kode Instalasi
                                                        <input
                                                            style="float: right; width: 40%; padding: 1px; text-align: center;"
                                                            id="kode_instalasi" disabled>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 50%; font-size: 14px; padding: 8px;"
                                                        class="text-black">
                                                        Alamat
                                                        <input
                                                            style="float: right; width: 40%; padding: 1px; text-align: center;"
                                                            id="alamat" disabled>
                                                    </td>
                                                    <td style="width: 50%; font-size: 14px; padding: 8px;">
                                                        Package
                                                        <input
                                                            style="float: right; width: 40%; padding: 1px; text-align: center;"
                                                            id="package" disabled>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="alert alert-light" role="alert">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="position-relative mb-3">
                                            <label for="tgl_transaksi">Tanggal Pelunasan</label>
                                            <input type="text" class="form-control date" name="tgl_transaksi"
                                                id="tgl_transaksi"value=" {{ date('d/m/Y') }}">
                                            <small class="text-danger" id="msg_tgl_transaksi"></small>
                                        </div>
                                    </div>
                                    <div class="d-none">
                                        <div class="position-relative mb-3">
                                            <label for="abodemen">Abodemen</label>
                                            <input type="text" class="form-control" id="abodemen" name="abodemen"
                                                readonly>
                                            <small class="text-danger" id="msg_abodemen"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="position-relative mb-3">
                                            <label for="biaya_sudah_dibayar">Biaya sudah dibayar</label>
                                            <input type="text" class="form-control" name="biaya_sudah_dibayar"
                                                id="biaya_sudah_dibayar" readonly>
                                            <small class="text-danger"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="position-relative mb-3">
                                            <label for="tagihan">Tagihan</label>
                                            <input type="text" class="form-control" name="tagihan" id="tagihan"
                                                readonly>
                                            <small class="text-danger"></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="position-relative mb-3">
                                            <label for="pembayaran">Pembayaran</label>
                                            <input type="text" class="form-control total" name="pembayaran"
                                                id="pembayaran" {!! $setting->swit_tombol_trx == '1' ? 'readonly' : '' !!} value="0.00">
                                            <small class="text-danger" id="msg_pembayaran"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative mb-3">
                                            <label for="total">Total</label>
                                            <input type="text" class="form-control" id="_total" readonly>
                                            <small class="text-danger"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-warning" type="button" id="BtndetailTransaksi">
                                    <span class="text">Detail</span>
                                </button>
                                <button class="btn btn-secondary btn-icon-split btn-struk" type="submit"
                                    id="simpanpembayaran" style="float: right; margin-left: 10px;">
                                    <span class="icon text-white-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-sign-intersection-fill" viewBox="0 0 16 16">
                                            <path
                                                d="M9.05.435c-.58-.58-1.52-.58-2.1 0L.436 6.95c-.58.58-.58 1.519 0 2.098l6.516 6.516c.58.58 1.519.58 2.098 0l6.516-6.516c.58-.58.58-1.519 0-2.098zM7.25 4h1.5v3.25H12v1.5H8.75V12h-1.5V8.75H4v-1.5h3.25z" />
                                        </svg>
                                    </span>
                                    <span class="text" style="float: right;">Simpan Pembayaran</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Modal detailTransaksi  tagihan-->
    <div class="modal fade" id="detailTransaksi" tabindex="-1" role="dialog" aria-labelledby="detailTransaksiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable"
            style="max-width: 100%; margin: 0; height: 100%;" role="document">
            <div class="modal-content" style="height: 100%; border-radius: 0;">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailTransaksiLabel">Detail Transaksi Pasang Baru</h5>
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

    <form action="/transactions/hapus" method="post" id="formHapus">
        @csrf
        <input type="hidden" name="del_id" id="del_id">
        <input type="hidden" name="del_istal_id" id="del_istal_id">
    </form>
@endsection

@section('script')
    {{-- BUG #3 FIX: Typeahead scripts (termasuk #PelunasanInstalasi handler)
         dipindahkan dari base ke partial ini. Hanya di-include di halaman
         yang punya element target. --}}
    @include('layouts.partials.typeahead-scripts')

    <script>
        //angka 00,000,00
        $("#abodemen").maskMoney({
            allowNegative: true
        });

        $("#biaya_instalasi").maskMoney({
            allowNegative: true
        });

        $("#tagihan").maskMoney({
            allowNegative: true
        });

        $(".total").maskMoney({
            allowNegative: true
        });

        //tanggal
        jQuery.datetimepicker.setLocale('de');
        $('.date').datetimepicker({
            i18n: {
                de: {
                    months: [
                        'Januar', 'Februar', 'März', 'April',
                        'Mai', 'Juni', 'Juli', 'August',
                        'September', 'Oktober', 'November', 'Dezember',
                    ],
                    dayOfWeek: [
                        "So.", "Mo", "Di", "Mi",
                        "Do", "Fr", "Sa.",
                    ]
                }
            },
            timepicker: false,
            format: 'd/m/Y'
        });

        //hitung _total
        $(document).on('change', '#pembayaran', function() {
            function cleanNumber(value) {
                let cleanNumber = value.toString().replace(/,/g, ''); // Remove commas
                return parseFloat(cleanNumber); // Convert back to number
            }

            var jumlah = cleanNumber($(this).val());
            var jumlah_bayar = cleanNumber($("#biaya_sudah_dibayar").val());
            var tagihan = cleanNumber($("#tagihan").val());
            var total = tagihan - (jumlah + jumlah_bayar);

            $("#_total").val(numFormat.format(Math.abs(total)));
        });

        //isi search tagihan to pelunasan
        var installation_id = "";

        if (installation_id > 0) {

            $.get('/transaksi/pelunasan_instalasi/' + installation_id, function(result) {
                installtaion(false, result)

                $('#id_instal').html(installation_id)
            })
        }

        //simpan
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
                            title: "Berhasil",
                            text: "Simpan Data Instalasi Berhasil",
                            icon: "success",
                            showDenyButton: true,
                            confirmButtonText: "Tambahkan Pembayaran Baru",
                            denyButtonText: "Kembali"
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

        //detail transaksi
        $(document).on('click', '#BtndetailTransaksi', function(e) {
            var id = $('#installation').val();
            var rek_debit = $('#rek_debit').val();
            var rek_kredit = $('#rek_kredit').val();

            if (id != '') {
                $.ajax({
                    url: '/transactions/detail_transaksi_instalasi',
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
                    text: 'Setelah menekan tombol Hapus Transaksi dibawah, maka transaksi ini akan dihapus dari aplikasi secara permanen.',
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
                                                '/transactions/pelunasan_instalasi';
                                        });
                                }

                            }
                        })
                    }
                })
            })
        })
    </script>
@endsection

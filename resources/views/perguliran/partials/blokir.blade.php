@extends('layouts.base')

@section('content')
    <!-- Form -->
    <form action="/installations/{{ $installation->id }}" method="post" id="Form_status_B">
        @csrf
        @method('PUT')
        <input type="text" name="status" id="status" value="{{ $installation->status }}" hidden>
        <input type="text" value="{{ number_format($tampil_settings->pasang_baru, 2) }}" name="pasang_baru" hidden>
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Bagian Informasi Customer -->
                        <div class="alert alert-success" role="alert">
                            <div class="row">
                                <div class="col-md-2 mb-2">
                                    <div class="col-md-3 text-center">
                                        <div class="d-inline-block border border-2 rounded bg-light shadow-sm"
                                            style="width: 120px; height: 120px; padding: 10px; display: flex; align-items: center; justify-content: left;">
                                            {!! $qr !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-10 mb-2">
                                    <h4 class="alert-heading">
                                        <b>Nama Pelanggan: {{ $installation->customer->nama }}</b>
                                    </h4>
                                    <hr>
                                    <p class="mb-0">
                                        Desa {{ $installation->village->nama }},
                                        {{ $installation->alamat }}, [Koordinate: {{ $installation->koordinate }}].
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel di Bawah Customer -->
                        <div class="mt-4">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="4">Detail Installation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">No.Induk</span>
                                            <span class="badge badge-warning"
                                                style="float: right; width: 30%; padding: 5px; text-align: center;">
                                                {{ $installation->kode_instalasi }}
                                                {{ substr($installation->package->kelas ?? '-', 0, 1) }}
                                            </span>
                                        </td>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Abodemen</span>
                                            <span class="badge badge-warning"
                                                style="float: right; width: 30%; padding: 5px; text-align: center;">
                                                {{ number_format($installation->abodemen, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Tgl Order</span>
                                            <span class="badge badge-warning"
                                                style="float: right; width: 30%; padding: 5px; text-align: center;">
                                                {{ $installation->order }}
                                            </span>
                                        </td>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Paket Instalasi</span>
                                            <span class="badge badge-warning"
                                                style="float: right; width: 30%; padding: 5px; text-align: center;">
                                                {{ $installation->package->kelas ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Tgl Pasang</span>
                                            <span class="badge badge-warning"
                                                style="float: right; width: 30%; padding: 5px; text-align: center;">
                                                {{ $installation->pasang }}
                                            </span>
                                        </td>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Status Instalasi</span>
                                            @if ($installation->status === 'B')
                                                <span class="badge badge-secondary"
                                                    style="float: right; width: 30%; padding: 5px; text-align: center;">
                                                    BLOKIR
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-light" role="alert">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="position-relative mb-3">
                                        <label for="cabut">Tentukan Tanggal Cabut</label>
                                        <input type="text" class="form-control date" name="cabut" id="cabut"
                                            value="{{ date('d/m/Y') }}">
                                        <small class="text-danger" id="msg_cabut"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative mb-3">
                                        <label for="kode_instalasi">Nik</label>
                                        <input type="text" class="form-control" name="kode_instalasi" id="kode_instalasi"
                                            value="{{ $installation->customer->nik }}" disabled>
                                        <small class="text-danger" id="msg_kode_instalasi"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative mb-3">
                                        <label for="kode_instalasi">desa</label>
                                        <input type="text" class="form-control" name="kode_instalasi" id="kode_instalasi"
                                            value="{{ $installation->village->nama }}" disabled>
                                        <small class="text-danger" id="msg_kode_instalasi"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <div class="me-auto">
                                @if (isset($jumlah_tunggakan) && $jumlah_tunggakan > 0)
                                    <span class="badge badge-danger p-2">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Masih ada {{ $jumlah_tunggakan }} tunggakan. Lunasi dulu sebelum aktifkan kembali.
                                    </span>
                                @else
                                    <span class="badge badge-success p-2">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Tidak ada tunggakan. Bisa diaktifkan kembali.
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-warning btn-icon-split" data-id="{{ $installation->id }}" type="button"
                                id="Kembali_Status_A" style="float: right; margin-left: 10px;"
                                @if (isset($jumlah_tunggakan) && $jumlah_tunggakan > 0) disabled title="Lunasi tunggakan terlebih dahulu" @endif>
                                <span class="icon text-white-50 d-none d-lg-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-sign-intersection-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M9.05.435c-.58-.58-1.52-.58-2.1 0L.436 6.95c-.58.58-.58 1.519 0 2.098l6.516 6.516c.58.58 1.519.58 2.098 0l6.516-6.516c.58-.58.58-1.519 0-2.098zM7.25 4h1.5v3.25H12v1.5H8.75V12h-1.5V8.75H4v-1.5h3.25z" />
                                    </svg>
                                </span>
                                <span class="text" style="float: right;">Kembali ke Aktif</span>
                            </button>

                            <button id="kembali"
                                class="btn btn-light btn-icon-split"style="float: right; margin-left: 10px;">
                                <span class="icon text-white-50 d-none d-lg-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-sign-turn-slight-left-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M9.05.435c-.58-.58-1.52-.58-2.1 0L.436 6.95c-.58.58-.58 1.519 0 2.098l6.516 6.516c.58.58 1.519.58 2.098 0l6.516-6.516c.58-.58.58-1.519 0-2.098zM6.864 8.368a.25.25 0 0 1-.451-.039l-1.06-2.882a.25.25 0 0 1 .192-.333l3.026-.523a.25.25 0 0 1 .26.371l-.667 1.154.621.373A2.5 2.5 0 0 1 10 8.632V11H9V8.632a1.5 1.5 0 0 0-.728-1.286l-.607-.364-.8 1.386Z" />
                                    </svg>
                                </span>
                                <span class="text">Kembali</span>
                            </button>
                            <button class="btn btn-secondary btn-icon-split" type="submit" id="Simpan_status_B"
                                style="float: right; margin-left: 10px;">
                                <span class="icon text-white-50 d-none d-lg-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-sign-intersection-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M9.05.435c-.58-.58-1.52-.58-2.1 0L.436 6.95c-.58.58-.58 1.519 0 2.098l6.516 6.516c.58.58 1.519.58 2.098 0l6.516-6.516c.58-.58.58-1.519 0-2.098zM7.25 4h1.5v3.25H12v1.5H8.75V12h-1.5V8.75H4v-1.5h3.25z" />
                                    </svg>
                                </span>
                                <span class="text" style="float: right;">Cabut Sekarang</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('script')
    <script>
        $(document).on('click', '#kembali', function(e) {
            e.preventDefault();
            window.location.href = '/installations?status=B';
        });

        $("#total").maskMoney({
            allowNegative: true
        });
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

        $(document).on('click', '#Simpan_status_B', function(e) {
            e.preventDefault();
            $('small').html('');

            var form = $('#Form_status_B');
            var actionUrl = form.attr('action');

            // Konfirmasi sebelum cabut: beri tahu user bahwa status tidak bisa dikembalikan lagi
            Swal.fire({
                title: 'Cabut Instalasi?',
                html: 'Setelah instalasi di-<b>CABUT</b>, status tidak dapat dikembalikan lagi ke status sebelumnya (Aktif/Blokir).<br><br>Pastikan ini adalah keputusan akhir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cabut Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#3085d6'
            }).then((confirmResult) => {
                if (!confirmResult.isConfirmed) {
                    Swal.fire({
                        title: 'Dibatalkan',
                        text: 'Instalasi tidak jadi dicabut.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success) {
                            Swal.fire({
                                title: result.msg,
                                text: 'Status instalasi telah berubah menjadi COPOT dan tidak dapat dikembalikan lagi.',
                                icon: "success",
                                draggable: true
                            }).then((res) => {
                                if (res.isConfirmed) {
                                    window.location.href = '/installations/' + result.cabut.id;
                                }
                            });
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
        });
        // mengembalikan status B ke A
        $(document).on('click', '#Kembali_Status_A', function(e) {
            e.preventDefault();

            var cek_id = $(this).attr('data-id');

            var actionUrl = '/installations/KembaliStatus_A/' + cek_id;

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data akan diproses dan tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Process",
                cancelButtonText: "No, Cancel",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#FormHapus')
                    $.ajax({
                        type: form.attr('method'),
                        url: actionUrl,
                        data: form.serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.msg,
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then((res) => {
                                if (res.isConfirmed) {
                                    window.location.reload()
                                } else {
                                    window.location.href = '/installations/' + response
                                        .kembaliA.id;
                                }
                            });
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            const errorMsg = response && response.msg
                                ? response.msg
                                : "Terjadi kesalahan.";
                            Swal.fire({
                                title: "Tidak Dapat Diaktifkan",
                                text: errorMsg,
                                icon: "warning",
                                confirmButtonText: "OK"
                            });
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Dibatalkan",
                        text: "Data Akan Tetap di Status BLOKIR",
                        icon: "info",
                        confirmButtonText: "OK"
                    });
                }
            });
        });
    </script>
@endsection

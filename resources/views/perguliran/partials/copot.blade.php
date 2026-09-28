@extends('layouts.base')

@section('content')
    <!-- Form -->
    <form action="/installations/{{ $installation->id }}" method="post" id="Form_status_C">
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
                                            <span style="float: left;">No. Induk</span>
                                            <span class="badge badge-danger"
                                                style="float: right; width:30%; padding: 5px; text-align: center;">
                                                {{ $installation->kode_instalasi }}
                                                {{ substr($installation->package->kelas ?? '-', 0, 1) }}
                                            </span>
                                        </td>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Abodemen</span>
                                            <span class="badge badge-danger"
                                                style="float: right; width:30%; padding: 5px; text-align: center;">
                                                {{ number_format($installation->abodemen, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Tgl Order</span>
                                            <span class="badge badge-danger"
                                                style="float: right; width:30%; padding: 5px; text-align: center;">
                                                {{ $installation->order }}
                                            </span>
                                        </td>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Paket Instalasi</span>
                                            <span class="badge badge-danger"
                                                style="float: right; width:30%; padding: 5px; text-align: center;">
                                                {{ $installation->package->kelas ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Tgl Pasang</span>
                                            <span class="badge badge-danger"
                                                style="float: right; width:30%; padding: 5px; text-align: center;">
                                                {{ $installation->pasang }}
                                            </span>
                                        </td>
                                        <td style="width: 50%; font-size: 14px; padding: 8px; position: relative;">
                                            <span style="float: left;">Status Instalasi</span>
                                            @if ($installation->status === 'C')
                                                <span class="badge badge-secondary"
                                                    style="float: right; width:30%; padding: 5px; text-align: center;">
                                                    CABUT
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
                        <div class="col-12 d-flex justify-content-end">
                            <button id="kembali" class="btn btn-light btn-icon-split">
                                <span class="icon text-white-50 d-none d-lg-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-sign-turn-slight-left-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M9.05.435c-.58-.58-1.52-.58-2.1 0L.436 6.95c-.58.58-.58 1.519 0 2.098l6.516 6.516c.58.58 1.519.58 2.098 0l6.516-6.516c.58-.58.58-1.519 0-2.098zM6.864 8.368a.25.25 0 0 1-.451-.039l-1.06-2.882a.25.25 0 0 1 .192-.333l3.026-.523a.25.25 0 0 1 .26.371l-.667 1.154.621.373A2.5 2.5 0 0 1 10 8.632V11H9V8.632a1.5 1.5 0 0 0-.728-1.286l-.607-.364-.8 1.386Z" />
                                    </svg>
                                </span>
                                <span class="text">Kembali</span>
                            </button>
                            <button type="button" id="HapusPelanggan" data-id="{{ $installation->id }}"
                                class="btn btn-danger btn-icon-split" style="margin-left: 10px;">
                                <span class="icon text-white-50 d-none d-lg-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z" />
                                    </svg>
                                </span>
                                <span class="text">Hapus Data Pelanggan</span>
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
            window.location.href = '/installations?status=C';
        });

        // Tombol Hapus Data Pelanggan (instalasi + usage + transaksi, TETAP simpan customer)
        $(document).on('click', '#HapusPelanggan', function(e) {
            e.preventDefault();

            var cek_id = $(this).attr('data-id');
            var csrfToken = '{{ csrf_token() }}';

            // Konfirmasi #1: Warning informasi risiko
            Swal.fire({
                title: 'Hapus Data Pelanggan?',
                html: `
                    <p class="text-left mb-2">
                        Tindakan ini akan <b>menghapus permanen</b> untuk instalasi ini:
                    </p>
                    <ul class="text-left mb-2">
                        <li>Data instalasi (No. Induk, paket, alamat, dll)</li>
                        <li>Seluruh riwayat pemakaian (usage)</li>
                        <li>Seluruh transaksi pembayaran terkait</li>
                    </ul>
                    <p class="text-left mb-2">
                        <b>Data customer (nama, NIK, foto, dll) tetap disimpan</b>
                        karena mungkin masih digunakan instalasi lain.
                    </p>
                    <p class="text-left text-danger mb-0">
                        <b>Tindakan ini tidak dapat dibatalkan.</b>
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Saya Mengerti, Lanjut',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result1) => {
                if (!result1.isConfirmed) {
                    Swal.fire({
                        title: 'Dibatalkan',
                        text: 'Data pelanggan tidak jadi dihapus.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Konfirmasi #2: User harus ketik "HAPUS" untuk konfirmasi ekstra
                Swal.fire({
                    title: 'Konfirmasi Terakhir',
                    html: `
                        <p>Ketik <b class="text-danger">HAPUS</b> pada kolom di bawah ini untuk melanjutkan penghapusan permanen.</p>
                        <input type="text" id="konfirmasi_hapus" class="swal2-input" placeholder="Ketik HAPUS" autocomplete="off">
                    `,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus Permanen',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    preConfirm: () => {
                        const inputVal = document.getElementById('konfirmasi_hapus').value.trim();
                        if (inputVal !== 'HAPUS') {
                            Swal.showValidationMessage('Anda harus mengetik "HAPUS" (huruf besar) untuk melanjutkan.');
                            return false;
                        }
                        return inputVal;
                    }
                }).then((result2) => {
                    if (!result2.isConfirmed) {
                        Swal.fire({
                            title: 'Dibatalkan',
                            text: 'Data pelanggan tidak jadi dihapus.',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Submit hapus permanen
                    $.ajax({
                        type: 'POST',
                        url: '/installations/HapusPelanggan/' + cek_id,
                        data: {
                            _token: csrfToken,
                            konfirmasi: result2.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.msg,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((res) => {
                                if (res.isConfirmed) {
                                    window.location.href = '/installations?status=C';
                                }
                            });
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            const errorMsg = response && response.msg
                                ? response.msg
                                : 'Terjadi kesalahan saat menghapus data.';
                            Swal.fire({
                                title: 'Gagal',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            });
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

        $(document).on('click', '#Simpan_status_C', function(e) {
            e.preventDefault();
            $('small').html('');

            var form = $('#Form_status_C');
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
                        }).then((res) => {
                            if (res.isConfirmed) {
                                window.location.href = '/installations/' + result.Pasang.id;
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
    </script>
@endsection

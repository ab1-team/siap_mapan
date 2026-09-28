@extends('layouts.base')
@section('content')
    <!-- Container Fluid-->
    <div class="container-fluid">
        <div class="row">
            <!-- Alerts Basic -->
            <div class="col-lg-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">PENGATURAN !</h6>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-pills">
                            <li class="nav-item w-100" style="margin-bottom: 10px;">
                                <a class="nav-link btn btn-white active" data-status="P" data-toggle="tab"
                                    data-target="#wellcome" href="#">
                                    <div class="left-align d-flex align-items-center">
                                        <i class="fas fa-home"></i>
                                        <span class="ml-2"><b>Wellcome</b></span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item w-100" style="margin-bottom: 10px;">
                                <a class="nav-link btn btn-white" data-status="L" data-toggle="tab" data-target="#lembaga"
                                    href="#">
                                    <div class="left-align">
                                        <i class="fas fa-cog"></i>
                                        <span class="ml-2"><b>Identitas Lembaga</b></span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item w-100" style="margin-bottom: 10px;">
                                <a class="nav-link btn btn-white" data-status="P" data-toggle="tab" data-target="#pasang"
                                    href="#">
                                    <div class="left-align d-flex align-items-center">
                                        <i class="fas fa-server"></i>
                                        <span class="ml-2"><b>Pasang Baru</b></span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item w-100" style="margin-bottom: 10px;">
                                <a class="nav-link btn btn-white" data-status="S" data-toggle="tab" data-target="#instalasi"
                                    href="#">
                                    <div class="left-align d-flex align-items-center">
                                        <i class="fas fa-file-alt"></i>
                                        <span class="ml-2"><b>Sistem Tagihan</b></span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item w-100" style="margin-bottom: 10px;">
                                <a class="nav-link btn btn-white" data-status="S" data-toggle="tab" data-target="#whatsapp"
                                    href="#">
                                    <div class="left-align d-flex align-items-center">
                                        <i class="fas fa-comment"></i>
                                        <span class="ml-2"><b>Whatsapp</b></span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item w-100" style="margin-bottom: 10px;">
                                <a class="nav-link btn btn-white" data-status="S" data-toggle="tab" data-target="#Logo"
                                    href="#">
                                    <div class="left-align d-flex align-items-center">
                                        <i class="fas fa-image"></i>
                                        <span class="ml-2"><b>Logo</b></span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div id="tabsContent" class="tab-content">
                    <!-- wellcome Tab -->
                    <div id="wellcome" role="tab" class="tab-pane active">
                        <!-- Content for wellcome -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="table-responsive p-3">
                                        @include('sop.partials.profil')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- lembaga Tab -->
                    <div id="lembaga" role="tab" class="tab-pane ">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="table-responsive p-3">
                                        @include('sop.partials.lembaga')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- pasang Tab -->
                    <div id="pasang" role="tab" class="tab-pane ">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="table-responsive p-3">
                                        @include('sop.partials.pasang_baru')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- instalasi Tab -->
                    <div id="instalasi" role="tab" class="tab-pane ">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="table-responsive p-3">
                                        @include('sop.partials.sistem_instal')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- whatsapp Tab -->
                    <div id="whatsapp" role="tab" class="tab-pane ">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title font-weight-bold">
                                            Pengaturan Pesan Whatsapp
                                        </h5>
                                        @include('sop.partials.whatsapp')

                                        <div class="d-flex justify-content-end">
                                            <button type="button" id="ScanWhatsapp" class="btn btn-info ml-2">
                                                Scan Whatsapp
                                            </button>
                                            <button type="button" id="HapusWhatsapp" class="btn btn-danger ml-2"
                                                style="display: none;">Hapus Whatsapp</button>
                                            <button type="button" id="SimpanWhatsapp" class="btn btn-primary ml-2">
                                                Simpan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="Logo" role="tab" class="tab-pane ">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="table-responsive p-3">
                                        @include('sop.partials.logo')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row -->
    </div>

    <form action="/pengaturan/whatsapp/{{ $token }}" method="post" id="FormWhatsapp">
        @csrf
    </form>
@endsection

@section('modal')
    <div class="modal fade" id="ModalScanWhatsapp" tabindex="-1" role="dialog"
        aria-labelledby="ModalScanWhatsappLabel" aria-modal="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalScanWhatsappLabel">Scan Whatsapp</h5>
                    <button type="button" class="close btn-modal-close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-5 col-lg-6 text-center">
                                    <img class="w-100 border-radius-lg shadow-lg mx-auto" src="/assets/img/no_image.png"
                                        id="QrCode" alt="chair">
                                </div>
                                <div class="col-lg-5 mx-auto">
                                    <h3 class="mt-lg-0 mt-4">Scan kode QR</h3>
                                    <ul class="list-group list-group-flush rounded" id="ListConnection">
                                        <li class="list-group-item">
                                            Membuat Kode QR
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-modal-close">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Handler upload logo dipindahkan ke partial `sop.partials.logo`
        // (alur: pilih file -> preview dulu -> klik Simpan baru upload).
        // Jangan bind handler di sini supaya tidak terjadi double upload.
    </script>
    <script>
        //pasang baru
        $("#pasang_baru").maskMoney({
            allowNegative: true
        });
        $(document).on('click', '#SimpanSwit', function(e) {
            e.preventDefault();
            var form = $('#Fromswit');
            var actionUrl = form.attr('action');

            $.ajax({
                type: 'GET',
                url: actionUrl,
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        toastMixin.fire({
                            title: 'Pembaruhan Pasang Baru Berhasil'
                        });
                        // setTimeout(() => window.location.reload(), 3000);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');
                }
            });
        });
    </script>
    <script>
        //identitas lembaga
        $(document).on('click', '#SimpanLembaga', function(e) {
            e.preventDefault();
            var form = $('#FromLembaga');
            var actionUrl = form.attr('action');

            $.ajax({
                type: 'GET',
                url: actionUrl,
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        toastMixin.fire({
                            title: 'Pembaruhan Identitas Lembaga Berhasil'
                        });
                        // setTimeout(() => window.location.reload(), 3000);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');
                }
            });
        });
    </script>
    <script>
        // sistem instal
        $("#abodemen").maskMoney({
            allowNegative: true
        });
        $("#denda").maskMoney({
            allowNegative: true
        });
        $("#biaya_aktivasi").maskMoney({
            allowNegative: true
        });

        $(document).on('click', '#SimpanInstal', function(e) {
            e.preventDefault();
            var form = $('#FromInstal');
            var actionUrl = form.attr('action');

            var toastMixin = Swal.mixin({
                toast: true,
                icon: 'success',
                position: 'top-right',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            $.ajax({
                type: 'GET',
                url: actionUrl,
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        toastMixin.fire({
                            title: 'Pembaruhan Sistem Tagihan Berhasil'
                        });
                        // setTimeout(() => window.location.reload(), 3000);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');
                }
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.min.js"></script>
    <script>
        let ListContainer = $('#ListConnection')
        const API = '{{ $api }}'
        const form = $('#FormWhatsapp')
        const socket = io(API, {
            transports: ['polling']
        })

        var scan = 0
        var connect = 0
        const pesan = $('#Pesan')

        var socketId = 0;
        socket.on('connected', (res) => {
            console.log('Connected to the server. Socket ID:', res.id);
            socketId = res.id
        });

        $('#HapusWhatsapp').hide()
        $('#ScanWhatsapp').hide()
        $(document).ready(function() {
            $.get(API + '/api/client/{{ $token }}', function(result) {
                if (result.success && result.data) {
                    $('#HapusWhatsapp').show()
                    $('#ScanWhatsapp').hide()
                } else {
                    $('#ScanWhatsapp').show()
                    $('#HapusWhatsapp').hide()
                }

                console.log(result);

            })
        })

        var scanQr = 0;
        socket.on('QR', (result) => {
            $('#QrCode').attr('src', result.url)

            if (scanQr <= 0) {
                var List = $('<li class="list-group-item font-weight-bold">Scan QR</li>')
                ListContainer.append(List)
            }

            scanQr += 1;
        })

        socket.on('ClientConnect', (result) => {
            $('#QrCode').attr('src', result.url)
            var List = $('<li class="list-group-item list-group-item-success font-weight-bold">Whatsapp Aktif</li>')
            ListContainer.append(List)
        })

        $(document).on('click', '#ScanWhatsapp', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: API + '/api/client',
                data: {
                    nama: $('#nama').val(),
                    token: '{{ $token }}',
                    socketId
                },
                success: function(result) {
                    if (result.success) {
                        $('#ModalScanWhatsapp').modal('show');
                    } else {
                        Swal.fire('Error', "Whatsapp sudah terdaftar.", 'error')
                    }
                }
            })
        });

        $(document).on('click', '#HapusWhatsapp', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Hapus Whatsapp',
                text: 'Hapus koneksi whatsapp.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                icon: 'error'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'DELETE',
                        url: API + '/api/client/{{ $token }}',
                        success: function(result) {
                            if (result.success) {
                                Swal.fire('Whatsapp Dihapus',
                                    "Scan ulang untuk bisa menggunakan layanan pesan pemberitahuan otomatis.",
                                    'success')

                                $('#ScanWhatsapp').show()
                                $('#HapusWhatsapp').hide()
                            }
                        }
                    })
                }
            })
        })

        $(document).on('click', '#SimpanWhatsapp', function(e) {
            e.preventDefault();

            var form = $('#FormScanWhatsapp');
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        toastMixin.fire({
                            title: result.msg
                        });
                    }
                }
            })
        })
    </script>
@endsection

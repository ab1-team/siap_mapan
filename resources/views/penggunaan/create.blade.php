@extends('layouts.base')
@php
    $label_search = 'Nama/Kode Installasi';

@endphp
@section('content')
    <form action="/usages" method="post" id="FormInputPemakaian">
        @csrf
        <input type="hidden" id="tgl_toleransi" value="{{ $settings->tanggal_toleransi }}">
        <input type="hidden" id="caters" value="{{ $cater_id }}">
        <input type="hidden" name="tanggal" id="tanggal" value="{{ $bulan }}">

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Bagian Informasi Customer -->
                        <div class="alert alert-info align-items-center text-white {{ auth()->user()->jabatan == '5' ? 'd-none' : 'd-flex mb-3' }}"
                            role="alert" style="border-radius: 1;">
                            <!-- Gambar -->
                            <img src="../../assets/img/air.png"
                                style="max-height: 160px; margin-right: 15px; margin-left: 10px;"
                                class="img-fluid d-none d-lg-block">

                            <div class="flex-grow-1">
                                <div class="mt-3">
                                    <h3><b>Input Pemakaian Air Bulanan</b></h3>
                                </div>
                                <hr class="my-2 bg-white">
                            </div>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="table align-items-center table-flush table-center table-hover" id="TbPemakain">
                                <thead class="thead-light" align="center">
                                    <tr>
                                        <th>NAMA</th>
                                        <th>DUSUN</th>
                                        <th>RT</th>
                                        <th>NO.INDUK</th>
                                        <th>METER AWAL</th>
                                        <th>METER AKHIR</th>
                                        <th>PEMAKAIAN</th>
                                    </tr>
                                </thead>
                                <tbody id="DaftarInstalasi">
                                    <!-- Data akan ditambahkan di sini -->
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <div class="col-12 justify-content-end d-flex">
                            <a href="/usages" class="btn btn-secondary">Kembali</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="modalScanQrCode" tabindex="-1" aria-labelledby="modalScanQrCodeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalScanQrCodeLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <video id="video" style="width: 100%; height: 100%;" autoplay muted playsinline></video>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div style="display: none;" id="print"></div>
    @include('penggunaan.partials.pemakaian')
@endsection

@section('script')
    <script type="text/javascript" src="https://unpkg.com/@zxing/browser@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@v5.0.1/dist/tesseract.min.js"></script>

    {{-- SCAN QR CODE PELANGGAN --}}
    <script type="module">
        import {
            BrowserMultiFormatReader
        } from 'https://cdn.jsdelivr.net/npm/@zxing/browser@0.0.10/+esm';

        document.addEventListener('DOMContentLoaded', function() {
            var codeReader = null;
            var readerControl = null;

            var btnScanQr = document.getElementById('scanQrCode');
            if (!btnScanQr) return;

            btnScanQr.addEventListener('click', async function() {
                var video = document.getElementById('video');
                codeReader = new BrowserMultiFormatReader();

                $('#modalScanQrCode').modal('show');

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    Swal.fire({
                        title: 'Tidak Didukung',
                        text: 'Browser Anda tidak mendukung akses kamera.',
                        icon: 'error',
                    });
                    return;
                }

                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');
                    let backCamera = videoDevices.find(device =>
                        device.label.toLowerCase().includes('back')
                    );
                    const selectedDeviceId = (backCamera || videoDevices[0])?.deviceId;

                    if (!selectedDeviceId) {
                        console.log('Tidak ada kamera yang tersedia.');
                        Swal.fire({
                            title: 'Kamera Tidak Tersedia',
                            text: 'Tidak ada kamera yang terdeteksi pada perangkat ini.',
                            icon: 'warning',
                        });
                        return;
                    }

                    readerControl = await codeReader.decodeFromVideoDevice(
                        selectedDeviceId,
                        video, (result, err, controls) => {
                            if (result) {
                                var idInstallation = result.getText();
                                $('#modalScanQrCode').modal('hide');
                                if (controls) controls.stop();

                                if (idInstallation) {
                                    var id_cater = $('#caters').val();
                                    var tanggal = $('#tanggal').val();

                                    Swal.fire({
                                        title: 'Mohon tunggu...',
                                        text: 'Sedang memproses data',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading()
                                        }
                                    });

                                    $.get(`/installations/cater/${id_cater}?tanggal=${tanggal}&installation_id=${idInstallation}`,
                                        function(result) {
                                            Swal.close();
                                            if (result && result.data && result.data.length > 0) {
                                                var installation = result.data[0];
                                                showModalInputPemakaian(installation);
                                            } else {
                                                Swal.fire({
                                                    title: 'Data Tidak Ditemukan',
                                                    text: 'Pastikan Kode Instalasi yang dimasukkan benar.',
                                                    icon: 'warning',
                                                });
                                            }
                                        });
                                }
                            }
                        });
                } catch (err) {
                    console.error('Gagal mengakses kamera:', err);
                    Swal.fire({
                        title: 'Gagal Mengakses Kamera',
                        text: err.message || 'Periksa izin kamera pada browser Anda.',
                        icon: 'error',
                    });
                }
            });

            $('#modalScanQrCode').on('hidden.bs.modal', function(e) {
                if (readerControl) {
                    readerControl.stop();
                    readerControl = null;
                }
            });
        });
    </script>

    <script>
        var videoScanMeter = null;
        var btnScanMeter = null;
        var previewCanvas = null;
        var previewCtx = null;
        var tmpCanvas = null;
        var tmpCtx = null;

        document.addEventListener('DOMContentLoaded', function() {
            videoScanMeter = document.getElementById('scanMeter');
            btnScanMeter = document.getElementById('btnScanMeter');
            previewCanvas = document.getElementById('previewImage');
            tmpCanvas = document.getElementById('tmpImage');

            if (previewCanvas) previewCtx = previewCanvas.getContext('2d');
            if (tmpCanvas) tmpCtx = tmpCanvas.getContext('2d');

            if (btnScanMeter) {
                btnScanMeter.addEventListener('click', async function() {
                    if (!videoScanMeter || videoScanMeter.videoWidth === 0 || videoScanMeter.videoHeight === 0) {
                        alert('Kamera scan belum siap');
                        return;
                    }

                    tmpCanvas.width = videoScanMeter.videoWidth;
                    tmpCanvas.height = videoScanMeter.videoHeight;

                    tmpCtx.filter = "contrast(250%) brightness(125%)";
                    tmpCtx.drawImage(videoScanMeter, 0, 0, tmpCanvas.width, tmpCanvas.height);

                    const scanX = tmpCanvas.width * 0.20;
                    const scanY = tmpCanvas.height * 0.40;
                    const scanWidth = tmpCanvas.width * 0.60;
                    const scanHeight = tmpCanvas.height * 0.20;

                    const previewImage = document.getElementById("previewImage");
                    previewImage.width = scanWidth;
                    previewImage.height = scanHeight;

                    const previewContex = previewImage.getContext("2d");
                    const imageData = tmpCtx.getImageData(scanX, scanY, scanWidth, scanHeight);
                    previewContex.putImageData(imageData, 0, 0);

                    try {
                        const {
                            data: {
                                text
                            }
                        } = await Tesseract.recognize(tmpCanvas, 'eng', {
                            logger: m => {
                                console.log(m);
                            },
                            tessedit_char_whitelist: '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ ',
                            preserve_interword_spaces: 1,
                            oem: 3,
                            psm: 4,
                        });

                        const hasilMatch = text.match(/\d+/);
                        const angka = hasilMatch ? hasilMatch[0] : "0";

                        console.log(text, angka);
                        $('.input-nilai-akhir').val(angka);
                    } catch (e) {
                        console.log(e.message);
                    }
                });
            }
        });

        function showModalInputPemakaian(installation) {
            if (!installation) return;

            var $akhir = installation.akhir ? $(installation.akhir) : $();
            var allowInput = $akhir.attr('data-allow-input') || false;

            var customer = installation.customer || {};
            var pkg = installation.package || {};
            var users = installation.users || {};
            var oneUsage = installation.one_usage || {};

            if (customer.jk == 'P') {
                $('.avatar-customer').attr('src', '{{ asset('assets/img/woman.png') }}')
            } else {
                $('.avatar-customer').attr('src', '{{ asset('assets/img/man.png') }}')
            }

            var inisialPaket = pkg.kelas ? pkg.kelas.charAt(0).toUpperCase() : '-';
            $('.namaCustomer').html(customer.nama || '-')
            $('.customer').val(installation.customer_id || '')
            $('.NikCustomer').html(customer.nik ? customer.nik : '-')
            $('.id_instalasi').val(installation.id || '')
            $('.AlamatCustomer').html((customer.alamat || '') + '.' + (customer.hp || ''))
            $('.KdInstallasi').html((installation.kode_instalasi || '') + ' ' + inisialPaket)
            $('.CaterInstallasi').html(users.nama || '-')
            $('.PackageInstallasi').html(pkg.kelas || '-')
            $('.AlamatInstallasi').html(installation.alamat || '-')
            $('.AkhirUsage').val(oneUsage.akhir || 0)
            $('.TglAkhirUsage').val(oneUsage.tgl_akhir || 0)
            $('.PemakaianUsage').val(oneUsage.tgl_pemakaian || 0)

            if (allowInput == 'false') {
                $('#SimpanPemakaian').attr('disabled', true)
            } else {
                $('#SimpanPemakaian').attr('disabled', false)
            }

            $('#staticBackdrop').modal('show')

            if (videoScanMeter && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment'
                    }
                }).then(function(stream) {
                    if (!videoScanMeter) return;
                    videoScanMeter.srcObject = stream;
                    videoScanMeter.play();

                    const videoTrack = stream.getVideoTracks()[0];
                    const settings = videoTrack ? videoTrack.getSettings() : {};

                    if (settings.facingMode === 'user') {
                        videoScanMeter.classList.add('mirror');
                    } else {
                        videoScanMeter.classList.remove('mirror');
                    }
                }).catch(function(err) {
                    console.error("Error accessing camera: " + err);
                });
            }
        }
    </script>

    <script>
        let dataInstallation;
        let dataSearch;
        let indexInput;
        let dataPemakaian = [];
        let dataTable;

        document.addEventListener('DOMContentLoaded', function() {
            var id_cater = $('#caters').val();
            var tanggal = $('#tanggal').val();

            if (!$.fn.DataTable) return;

            dataTable = $('#TbPemakain').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '/installations/cater/' + id_cater + '?tanggal=' + tanggal,
                    "type": "GET"
                },
                "language": {
                    "processing": `<i class="fas fa-spinner fa-spin"></i> Mohon Tunggu....`,
                    "emptyTable": "Tidak ada data yang tersedia",
                    "search": "",
                    "searchPlaceholder": "Pencarian...",
                    "paginate": {
                        "next": "<i class='fas fa-angle-right'></i>",
                        "previous": "<i class='fas fa-angle-left'></i>"
                    }
                },
                "columns": [{
                    data: 'customer.nama',
                    defaultContent: '-'
                }, {
                    data: 'village.dusun',
                    defaultContent: '-'
                }, {
                    data: 'rt',
                    defaultContent: '-'
                }, {
                    data: 'kode_instalasi',
                    render: function(data, type, row) {
                        var inisial = (row.package && row.package.kelas) ? row.package.kelas.charAt(0).toUpperCase() : '-';
                        return `${data || ''}.${inisial}`;
                    }
                }, {
                    data: 'one_usage.akhir',
                    render: function(data, type, row) {
                        var nilai_awal = data || '0';
                        return `<div class="text-right">${nilai_awal}</div>`
                    }
                }, {
                    data: 'akhir',
                    defaultContent: ''
                }, {
                    data: 'one_usage.jumlah',
                    render: function(data, type, row) {
                        return `<div class="text-right">0</div>`
                    }
                }],
                columnDefs: [{
                    targets: [4, 5, 6],
                    searchable: false,
                    orderable: false,
                }],
            })

            var $filter = $('#TbPemakain_filter');
            if ($filter.length) {
                $filter.html(`
                    <div class="input-group">
                        <input type="text" id="customSearch" class="form-control" placeholder="Cari Pelanggan...">
                        <div class="input-group-append">
                            <button class="btn btn-info mt-0" type="button" id="scanQrCode"><i class="fas fa-qrcode"></i></button>
                        </div>
                    </div>
                `);
            }

            $(document).on('keyup', '#customSearch', function() {
                if (dataTable) dataTable.search(this.value).draw();
            });

            $(document).ready(function() {
                $('.select2').select2({
                    theme: 'bootstrap4',
                });
            });

            const alert = document.getElementById('success-alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.add('d-none');
                }, 5000);
            }

            $(document).on('change', '.hitungan', function() {
                var awal = $('#awal').val()
                var akhir = $('#akhir').val()

                if (akhir - awal < 0 || akhir == '') {
                    return;
                }
            });

            if ($.datetimepicker) {
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
                                "Do.", "Fr", "Sa.",
                            ]
                        }
                    },
                    timepicker: false,
                    format: 'd/m/Y'
                });
            }

            $(document).on('change', '.tanggal', function(e) {
                $('.tanggal').val($(this).val());
            })

            $('#TbPemakain').on('click', 'tbody tr', function(e) {
                if (!dataTable) return;
                var installation = dataTable.row(this).data();

                showModalInputPemakaian(installation);
            })

            $(document).on('focus', '.input-nilai-akhir', function(e) {
                e.preventDefault();
                $(this).select();
            });

            $(document).on('change', '.input-nilai-akhir', function(e) {
                e.preventDefault()

                var id = $(this).attr('id').split('_')[1]
                var nilai_akhir = $(this).val()
                var nilai_awal = $('#awal_' + id).val()

                if (nilai_akhir - nilai_awal < 0) {
                    Swal.fire({
                        title: 'Periksa kembali nilai yang dimasukkan',
                        text: 'Nilai Akhir tidak boleh lebih kecil dari Nilai Awal',
                        icon: 'warning',
                    })

                    $(this).val(nilai_awal)
                    return;
                }

                var jumlah = nilai_akhir - nilai_awal
                $('#jumlah_' + id).val(jumlah)
            })

            $(document).on('click', '#SimpanPemakaian', function(e) {
                e.preventDefault();

                var id_cater = $('#caters').val();
                var customer = $('#customer').val();
                var awal = $('#awal_').val();
                var akhir = $('#akhir_').val();
                var jumlah = $('#jumlah_').val();
                var id = $('#id_instalasi').val();
                var tgl = $('#tanggal').val();
                var toleransi = $('#tgl_toleransi').val();

                var data = {
                    id: id,
                    tgl_pemakaian: tgl,
                    id_cater: id_cater,
                    customer: customer,
                    awal: awal,
                    akhir: akhir,
                    jumlah: jumlah,
                    toleransi: toleransi
                };

                var form = $('#FormInputPemakaian');
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: {
                        _token: form.find('input[name="_token"]').val(),
                        tanggal: $('#tanggal').val(),
                        data: data
                    },
                    success: function(result) {
                        if (result.success) {
                            toastMixin.fire({
                                title: 'Pemakaian Berhasil di Input'
                            });

                            $('#staticBackdrop').modal('hide');
                            if (dataTable) dataTable.ajax.reload();
                        } else {
                            Swal.fire({
                                title: 'Gagal Menyimpan',
                                text: result.msg || 'Terjadi kesalahan yang tidak diketahui.',
                                icon: 'error',
                            });
                        }
                    },
                    error: function(xhr) {
                        // Tampilkan pesan error dari server (jika ada) atau fallback
                        var msg = 'Terjadi kesalahan saat menyimpan.';
                        try {
                            var res = xhr.responseJSON;
                            if (res && res.msg) {
                                msg = res.msg;
                            }
                        } catch (e) {}

                        Swal.fire({
                            title: 'Gagal Menyimpan',
                            text: msg,
                            icon: 'error',
                        });
                    }
                });
            });
        });
    </script>
@endsection

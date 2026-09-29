@extends('layouts.base')

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="row">
            <div class="col-lg-12">
                {{-- Header Section --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-undo" style="font-size: 30px; margin-right: 13px; color: #6777ef;"></i>
                        <b>Reversal Piutang Tunggakan</b>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (!$kodePiutang)
                    <div class="alert alert-danger">
                        Akun piutang (<code>1.1.03.01</code>) belum diset di COA. Menu ini tidak dapat digunakan.
                    </div>
                @endif

                @if ($kodePiutang && $installations->isEmpty())
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Tidak ada piutang tunggakan yang perlu di-reversal</h5>
                            <p class="text-muted">
                                Semua pemakaian berstatus <strong>UNPAID</strong> sudah tercatat benar di
                                transaksi piutang tunggakan. Tidak ada mismatch antara status aplikasi dengan
                                mutasi bank.
                            </p>
                        </div>
                    </div>
                @endif

                @if ($kodePiutang && $installations->isNotEmpty())
                    {{-- Info & Filter Card --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="alert alert-light mb-3" role="alert">
                                <div class="row align-items-center">
                                    <div class="col-md-9">
                                        <i class="fas fa-info-circle text-primary"></i>
                                        Daftar instalasi yang memiliki piutang tunggakan (pemakaian berstatus
                                        <span class="badge badge-warning">UNPAID</span>). Admin dapat mengecek
                                        mutasi bank, lalu me-reversal piutang untuk pelanggan yang sebenarnya
                                        sudah membayar via transfer sebelum generate tunggakan berjalan.
                                    </div>
                                    <div class="col-md-3 text-right">
                                        @if ($totalAll > 0)
                                            <small class="text-muted d-block">Total Piutang</small>
                                            <h5 class="text-danger font-weight-bold mb-0">
                                                Rp {{ number_format($totalAll, 0, ',', '.') }}
                                            </h5>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <form method="GET" action="/transactions/reversal_piutang" id="formFilter">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tahun">Tahun</label>
                                            <select class="js-select-2 form-control" name="tahun" id="tahun">
                                                <option value="">--- Semua Tahun ---</option>
                                                @for ($y = date('Y'); $y >= 2024; $y--)
                                                    <option value="{{ $y }}"
                                                        {{ (string) ($filterTahun ?? '') === (string) $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="bulan">Bulan</label>
                                            <select class="js-select-2 form-control" name="bulan" id="bulan">
                                                <option value="">--- Semua Bulan ---</option>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}"
                                                        {{ (string) ($filterBulan ?? '') === (string) $m ? 'selected' : '' }}>
                                                        {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}.
                                                        {{ \App\Utils\Tanggal::namaBulan('2025-' . str_pad($m, 2, '0', STR_PAD_LEFT) . '-01') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="q">Pencarian</label>
                                            <input type="text" name="q" id="q"
                                                value="{{ $search ?? '' }}"
                                                class="form-control"
                                                autocomplete="off"
                                                placeholder="Kode Instalasi / Nama Pelanggan">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                @if ($filterTahun || $filterBulan || ($search ?? ''))
                                                    <a href="/transactions/reversal_piutang"
                                                        class="btn btn-secondary btn-sm" title="Reset Filter">
                                                        <i class="fas fa-times"></i> Reset
                                                    </a>
                                                @else
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i>
                                                        Filter otomatis
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if ($totalRows > 0)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Halaman <strong>{{ $page }}</strong> dari
                                        <strong>{{ $totalPages }}</strong>
                                        ({{ number_format($totalRows) }} instalasi)
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tabel Data --}}
                    <div class="card mb-4">
                        <div class="table-responsive p-3">
                            <table class="table align-items-center table-flush table-hover" id="TbReversalPiutang">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Kode Instalasi</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Desa</th>
                                        <th>Paket</th>
                                        <th class="text-right">Piutang Tunggakan</th>
                                        <th>Piutang Terakhir</th>
                                        <th style="text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($installations as $i => $ins)
                                        <tr>
                                            <td>{{ ($page - 1) * $perPage + $i + 1 }}</td>
                                            <td><code>{{ $ins->kode_instalasi }}</code></td>
                                            <td>{{ $ins->customer->nama ?? '-' }}</td>
                                            <td>{{ $ins->village->nama ?? '-' }}</td>
                                            <td>{{ $ins->package->paket ?? '-' }}</td>
                                            <td class="text-right text-danger font-weight-bold">
                                                Rp {{ number_format($ins->total_piutang, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $ins->tgl_akhir_max ? date('d/m/Y', strtotime($ins->tgl_akhir_max)) : '-' }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ count($ins->piutang_per_usage ?? []) }} bulan UNPAID
                                                </small>
                                            </td>
                                            <td style="text-align: center;">
                                                <button class="btn btn-warning btn-sm btn-detail-reversal"
                                                    data-id="{{ $ins->id }}" title="Lihat Detail Piutang">
                                                    <i class="fas fa-search"></i> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if ($totalPages > 1)
                            @php
                                $queryParams = [];
                                if ($filterTahun) {
                                    $queryParams['tahun'] = $filterTahun;
                                }
                                if ($filterBulan) {
                                    $queryParams['bulan'] = $filterBulan;
                                }
                                if ($search) {
                                    $queryParams['q'] = $search;
                                }
                                $prevUrl = '/transactions/reversal_piutang?' . http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)]));
                                $nextUrl = '/transactions/reversal_piutang?' . http_build_query(array_merge($queryParams, ['page' => min($totalPages, $page + 1)]));
                            @endphp
                            <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                                <small class="text-muted">
                                    Menampilkan {{ ($page - 1) * $perPage + 1 }} -
                                    {{ min($page * $perPage, $totalRows) }} dari
                                    {{ number_format($totalRows) }}
                                </small>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        @if ($page > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $prevUrl }}">
                                                    &laquo; Sebelumnya
                                                </a>
                                            </li>
                                        @endif
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }} / {{ $totalPages }}</span>
                                        </li>
                                        @if ($page < $totalPages)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $nextUrl }}">
                                                    Selanjutnya &raquo;
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetailReversal" tabindex="-1" role="dialog"
        aria-labelledby="modalDetailReversalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailReversalLabel">
                        <i class="fas fa-undo"></i> Detail Piutang Tunggakan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalDetailReversalBody">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success" id="btnProsesReversal" disabled>
                        <i class="fas fa-undo"></i> Proses Reversal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="formReversalPiutang" method="POST" action="/transactions/reversal_piutang/proses">
        @csrf
        <div id="containerUsageIds"></div>
    </form>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 dengan tema bootstrap4 (konsisten dgn app lain)
            if ($.fn.select2) {
                $('.js-select-2').select2({
                    theme: 'bootstrap4',
                });
            }

            // Auto-submit form filter (tanpa tombol Cari):
            // - Tahun & Bulan: langsung submit saat value berubah
            // - Pencarian: debounce 500ms agar tidak submit tiap ketukan keyboard
            var formFilter = document.getElementById('formFilter');
            var debounceTimer = null;

            $('#tahun, #bulan').on('change', function() {
                // Reset page ke 1 setiap ganti filter
                if (formFilter.querySelector('input[name="page"]')) {
                    formFilter.querySelector('input[name="page"]').value = 1;
                }
                formFilter.submit();
            });

            $('#q').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    if (formFilter.querySelector('input[name="page"]')) {
                        formFilter.querySelector('input[name="page"]').value = 1;
                    }
                    formFilter.submit();
                }, 500);
            });

            // Init DataTables jika tabel ada (untuk sorting/filter di klien)
            // Catatan: server-side pagination tetap aktif via ?page= di URL.
            if ($.fn.DataTable && document.getElementById('TbReversalPiutang')) {
                $('#TbReversalPiutang').DataTable({
                    "paging": false, // Kita pakai server-side pagination sendiri
                    "info": false,
                    "searching": false, // Kita pakai server-side search sendiri
                    "ordering": true,
                    "language": {
                        "emptyTable": "Tidak ada data piutang"
                    }
                });
            }

            // Klik tombol Detail -> buka modal & load via AJAX
            $(document).on('click', '.btn-detail-reversal', function() {
                var installationId = $(this).data('id');
                $('#modalDetailReversalBody').html(
                    '<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>'
                );
                $('#modalDetailReversal').modal('show');
                $('#btnProsesReversal').prop('disabled', true);

                $.ajax({
                    url: '/transactions/reversal_piutang/detail/' + installationId,
                    type: 'GET',
                    success: function(result) {
                        if (result.success) {
                            $('#modalDetailReversalBody').html(result.view);
                            updateButtonState();
                        } else {
                            $('#modalDetailReversalBody').html(
                                '<div class="alert alert-danger">' + result.msg + '</div>'
                            );
                        }
                    },
                    error: function() {
                        $('#modalDetailReversalBody').html(
                            '<div class="alert alert-danger">Gagal memuat detail.</div>'
                        );
                    }
                });
            });

            // Toggle tombol Proses berdasarkan checkbox
            $(document).on('change', '.usage-checkbox', function() {
                updateButtonState();
            });

            function updateButtonState() {
                var checked = $('.usage-checkbox:checked').length;
                $('#btnProsesReversal').prop('disabled', checked === 0);
            }

            // Klik tombol Proses -> konfirmasi & submit AJAX
            $(document).on('click', '#btnProsesReversal', function() {
                var checkedBoxes = $('.usage-checkbox:checked');
                if (checkedBoxes.length === 0) return;

                var usageIds = [];
                checkedBoxes.each(function() {
                    usageIds.push($(this).val());
                });

                Swal.fire({
                    title: 'Konfirmasi Reversal',
                    html: 'Akan menghapus piutang tunggakan pada <strong>' + usageIds.length +
                        '</strong> bulan pemakaian.<br><br>' +
                        '<small class="text-danger">Pastikan data pembayaran pelanggan sudah benar-benar masuk. ' +
                        'Tindakan ini tidak dapat dibatalkan.</small>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses Reversal',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#containerUsageIds').empty();
                        usageIds.forEach(function(id) {
                            $('#containerUsageIds').append(
                                '<input type="hidden" name="usage_ids[]" value="' + id + '">'
                            );
                        });

                        var form = $('#formReversalPiutang');
                        $.ajax({
                            type: 'POST',
                            url: form.attr('action'),
                            data: form.serialize(),
                            success: function(result) {
                                if (result.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: result.msg,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Gagal', result.msg, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.msg ||
                                    'Terjadi kesalahan server', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
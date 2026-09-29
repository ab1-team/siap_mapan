{{-- Info Instalasi --}}
<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <th width="35%" class="text-muted">Kode Instalasi</th>
                <td><code class="text-primary">{{ $installation->kode_instalasi }}</code></td>
            </tr>
            <tr>
                <th class="text-muted">Nama Pelanggan</th>
                <td>{{ $installation->customer->nama ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">Desa</th>
                <td>{{ $installation->village->nama ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">Paket</th>
                <td>
                    {{ $installation->package->paket ?? '-' }}
                    <small class="text-muted">
                        (Rp {{ number_format($installation->abodemen ?? 0, 0, ',', '.') }})
                    </small>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-6 text-right">
        <small class="text-muted d-block">Total Piutang</small>
        <h3 class="text-danger font-weight-bold mb-0">
            Rp {{ number_format($usages->sum('total_piutang'), 0, ',', '.') }}
        </h3>
        <small class="text-muted">dari {{ $usages->count() }} bulan</small>
    </div>
</div>

<hr>

<div class="alert alert-info small mb-3">
    <i class="fas fa-info-circle"></i>
    <strong>Petunjuk:</strong> Centang bulan yang akan di-reversal. Tindakan ini akan:
    <ul class="mb-0 mt-1">
        <li>Menghapus transaksi piutang tunggakan (D 1.1.03.01 / K 4.1.01.02|03|04) bulan terkait.</li>
        <li>Menghitung ulang saldo akun bulan terkait secara otomatis.</li>
    </ul>
    Status pemakaian (UNPAID/PAID) tidak berubah. Piutang bulan-bulan sebelumnya yang memang belum
    dibayar tetap aman.
</div>

<form id="formDetailReversal">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th width="5%" class="text-center">Pilih</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Tgl Akhir</th>
                    <th class="text-right">Total Piutang</th>
                    <th>Rincian Akun</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usages as $usage)
                    <tr>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox" class="form-check-input usage-checkbox"
                                    value="{{ $usage->id }}" data-total="{{ $usage->total_piutang }}">
                            </div>
                        </td>
                        <td>
                            <strong>{{ \App\Utils\Tanggal::namaBulan($usage->tgl_akhir) }}
                                {{ \App\Utils\Tanggal::tahun($usage->tgl_akhir) }}</strong>
                            <br>
                            <small class="text-muted">
                                Meter: {{ $usage->awal ?? 0 }} → {{ $usage->akhir ?? 0 }}
                                ({{ $usage->jumlah ?? 0 }} m³)
                            </small>
                        </td>
                        <td>
                            <span
                                class="badge badge-{{ $usage->status == 'PAID' ? 'success' : 'warning' }}">
                                {{ $usage->status }}
                            </span>
                        </td>
                        <td>{{ date('d/m/Y', strtotime($usage->tgl_akhir)) }}</td>
                        <td class="text-right font-weight-bold text-danger">
                            Rp {{ number_format($usage->total_piutang, 0, ',', '.') }}
                        </td>
                        <td>
                            <ul class="list-unstyled mb-0 small">
                                @foreach ($usage->trx_piutang as $trx)
                                    <li class="mb-1">
                                        <i class="fas fa-arrow-right text-danger"></i>
                                        <code>D {{ $trx->rek_debit->kode_akun ?? '-' }}</code>
                                        {{ $trx->rek_debit->nama_akun ?? '-' }}
                                        /
                                        <code>K {{ $trx->rek_kredit->kode_akun ?? '-' }}</code>
                                        {{ $trx->rek_kredit->nama_akun ?? '-' }}
                                        = <strong>Rp {{ number_format($trx->total, 0, ',', '.') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $trx->keterangan }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Tidak ada data piutang yang perlu di-reversal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
@php
    use App\Utils\Tanggal;
@endphp

@include('pelaporan.layouts.style')
<title>{{ $title }} {{ $sub_judul }}</title>

@foreach ($caters as $cater)
    @php
        $filterInstalasi = $cater->installations->filter(fn($ins) => $ins->usage->count() > 0);
        if ($filterInstalasi->isEmpty()) {
            continue;
        }
    @endphp

    @if ($loop->iteration > 1)
        <div class="break"></div>
    @endif

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px; font-weight: bold;">
                    {{ strtoupper('Piutang Pelanggan ' . $cater->nama) }}
                </div>
                <div style="font-size: 16px; font-weight: bold;">{{ strtoupper($sub_judul) }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="10"></td>
        </tr>
    </table>

    <table border="0" width="100%">
        <thead>
            <tr style="background-color: rgb(230, 230, 230); font-weight: bold;">
                <th width="4%" class="t l b" rowspan="2">No</th>
                <th width="21%" class="t l b" rowspan="2">Nama</th>
                <th width="15%" class="t l b" rowspan="2">No. Induk</th>
                <th width="30%" class="t l b" colspan="3">Tunggakan</th>
                <th width="15%" class="t l b" rowspan="2">Jumlah Tunggakan</th>
                <th width="15%" class="t l b r" rowspan="2">Kategori</th>
            </tr>
            <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                <th width="10%" class="t l b">s/d 3 Bulan Lalu</th>
                <th width="10%" class="t l b">Bulan Lalu</th>
                <th width="10%" class="t l b">Bulan Ini</th>
            </tr>
        </thead>

        <tbody>
            @php
                $data_desa = [];
                $section = '';
            @endphp
            @foreach ($filterInstalasi as $ins)
                @if (!in_array($ins->desa, $data_desa))
                    @if ($section != $ins->desa && count($data_desa) > 0)
                        <tr class="bold">
                            <td class="t l b" colspan="3" align="right">Jumlah</td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_menunggak_sampai_bulan_lalu, 2) }}
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_menunggak_bulan_lalu, 2) }}
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_menunggak_bulan_ini, 2) }}
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_tunggakan, 2) }}
                            </td>
                            <td class="t l b r"></td>
                        </tr>
                    @endif

                    <tr class="bold">
                        <td class="t l b r" colspan="9" height="25">
                            Desa {{ $ins->village->nama }} Dusun {{ $ins->village->dusun }}
                        </td>
                    </tr>

                    @php
                        $data_desa[] = $ins->desa;
                        $section = $ins->desa;

                        $nomor = 1;
                        $jumlah_menunggak_sampai_bulan_lalu = 0;
                        $jumlah_menunggak_bulan_lalu = 0;
                        $jumlah_menunggak_bulan_ini = 0;
                        $jumlah_bayar = 0;
                        $jumlah_tunggakan = 0;
                    @endphp
                @endif

                @php
                    $bayar = 0;
                    $sampai_bulan_lalu = 0;
                    $bulan_lalu = 0;
                    $bulan_ini = 0;
                    $jumlah_menunggak = 0;
                    foreach ($ins->usage as $usage) {
                        $denda_sampai_bulan_lalu = 0;
                        $denda_bulan_lalu = 0;
                        $denda_bulan_ini = 0;

                        foreach ($usage->transaction as $trx) {
                            $bulan_tagihan = date('Y-m', strtotime($usage->tgl_akhir)) . '-01';
                            $bulan_kondisi = date('Y-m', strtotime($tgl_kondisi)) . '-01';
                            $bulan_kondisi_lalu = date('Y-m', strtotime('-1 month', strtotime($bulan_kondisi))) . '-01';
                            $bulan_kondisi_lama =
                                date('Y-m', strtotime('-3 month', strtotime($bulan_kondisi))) . '-01';

                            if ($trx->rekening_debit == $akun_piutang->id) {
                                if ($trx->rekening_kredit == $akun_denda->id) {
                                    if ($bulan_tagihan < $bulan_kondisi_lama) {
                                        $denda_sampai_bulan_lalu += $trx->total;
                                    } elseif ($bulan_tagihan < $bulan_kondisi_lalu) {
                                        $denda_bulan_lalu += $trx->total;
                                    } elseif ($bulan_tagihan < $bulan_kondisi) {
                                        $denda_bulan_ini += $trx->total;
                                    }
                                } else {
                                    if ($bulan_tagihan < $bulan_kondisi_lalu) {
                                        $sampai_bulan_lalu += $trx->total;
                                    } elseif ($bulan_tagihan < $bulan_kondisi) {
                                        $bulan_lalu += $trx->total;
                                    } else {
                                        $bulan_ini += $trx->total;
                                    }
                                }
                            } else {
                                $bayar += $trx->total;
                            }
                        }

                        $sampai_bulan_lalu += $denda_sampai_bulan_lalu;
                        $bulan_lalu += $denda_bulan_lalu;
                        $bulan_ini += $denda_bulan_ini;

                        $jumlah_menunggak += 1;
                    }

                    $tunggakan = $sampai_bulan_lalu + $bulan_lalu + $bulan_ini;
                    $status = 'Lancar';
                    if ($jumlah_menunggak > 0) {
                        $status = 'Menunggak';
                    }

                    if ($jumlah_menunggak > 1) {
                        $status = 'SP';
                    }

                    if ($jumlah_menunggak > 2) {
                        $status = 'SPS';
                    }

                    $jumlah_menunggak_sampai_bulan_lalu += $sampai_bulan_lalu;
                    $jumlah_menunggak_bulan_lalu += $bulan_lalu;
                    $jumlah_menunggak_bulan_ini += $bulan_ini;
                    $jumlah_tunggakan += $tunggakan;
                    $jumlah_bayar += $bayar;

                    if ($tunggakan <= 0) {
                        continue;
                    }
                @endphp

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b">{{ $ins->customer->nama }}</td>
                    <td class="t l b">{{ $ins->kode_instalasi }}</td>
                    <td class="t l b" align="right">
                        {{ number_format($sampai_bulan_lalu, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($bulan_lalu, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($bulan_ini, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($tunggakan, 2) }}
                    </td>
                    <td class="t l b r" align="center">
                        {{ $status }}
                    </td>
                </tr>
            @endforeach
            <tr class="bold">
                <td class="t l b" colspan="3" align="right">Jumlah</td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_menunggak_sampai_bulan_lalu, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_menunggak_bulan_lalu, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_menunggak_bulan_ini, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_tunggakan, 2) }}
                </td>
                <td class="t l b r"></td>
            </tr>
        </tbody>
    </table>
@endforeach

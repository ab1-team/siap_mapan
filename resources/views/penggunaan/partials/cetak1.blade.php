@php
    use App\Utils\Tanggal;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        * {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background-color: #eee;
        }

        .no-border td {
            border: none;
            padding: 2px 4px;
        }

        .break {
            page-break-after: always;
        }
    </style>
</head>
@php
    // Kelompokkan usages berdasarkan dusun
    $usagesByDusun = $usages->groupBy(function ($usage) {
        return $usage->installation->village->dusun ?? '-';
    });
@endphp


<body>
    @php
        $indexes = 1;
    @endphp
    @foreach ($usagesByDusun as $dusun => $usagesGroup)
        @php
            $subtotal = 0;
            $totalPemakaian = 0;
            $totalPaid = 0;
            $totalUnpaid = 0;
        @endphp
        @if ($indexes > 1)
            <div class="break"></div>
        @endif
        <!-- HEADER - letakkan di luar table -->
        <div style="text-align: center; max-width: 100%;">
            <div style="font-size: 14px; margin-bottom: 2px;"><b>DAFTAR TAGIHAN PEMAKAIAN AIR</b></div>
            <div style="font-size: 18px; margin-bottom: 2px;"><b>"{{ strtoupper($bisnis->nama) }}" BUMDes BANGUN
                    KENCANA</b>
            </div>
            <div style="font-size: 14px; margin-bottom: 5px;">KALURAHAN MULO KAPANEWON WONOSARI</div>
        </div>
        <div style="border-bottom: 2px solid #000; margin: 4px 0;"></div>

        @php
            $lastUsageInGroup = $usagesGroup->sortByDesc('tgl_akhir')->first();
            $tglAkhirFormatted = $lastUsageInGroup
                ? \Carbon\Carbon::parse($lastUsageInGroup->tgl_akhir)->subDay()->translatedFormat('F d Y')
                : '-';
        @endphp

        <table style="width: 100%; margin-top: 10px; font-size: 12px; border-collapse: collapse; border: none;">
            <table style="width: 100%; margin-top: 10px; font-size: 12px; border-collapse: collapse;">
                <tr>
                    <td style="width: 15%; border: none; padding: 2px 4px; line-height: 1.1;">Bulan Pemakaian</td>
                    <td style="width: 1%; border: none; padding: 2px 4px; line-height: 1.1;">:</td>
                    <td style="width: 40%; border: none; padding: 2px 4px; line-height: 1.1;"><b>{{ $bulan }}</b>
                    </td>

                    <td style="width: 17%; border: none; padding: 2px 4px; line-height: 1.1;">Tgl Akhir Pembayaran</td>
                    <td style="width: 1%; border: none; padding: 2px 4px; line-height: 1.1;">:</td>
                    <td style="width: 15%; border: none; padding: 2px 4px; line-height: 1.1;">
                        <b>{{ $tglAkhirFormatted }}</b>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 4px; line-height: 1.1;">Cater</td>
                    <td style="border: none; padding: 2px 4px; line-height: 1.1;">:</td>
                    <td style="border: none; padding: 2px 4px; line-height: 1.1;"><b>{{ $pemakaian_cater ?? '-' }}</b>
                    </td>

                    <td style="border: none; padding: 2px 4px; line-height: 1.1;"> Dusun</td>
                    <td style="border: none; padding: 2px 4px; line-height: 1.1;">:</td>
                    <td style="border: none; padding: 2px 4px; line-height: 1.1;"><b>{{ $dusun }}</b></td>
                </tr>
            </table>
        </table><br>
        <table>
            <thead>
                <tr>
                    <th style="text-align: center;">No</th>
                    <th style="text-align: center;">Nama</th>
                    <th style="text-align: center;">No. Induk</th>
                    <th style="text-align: center;">RT</th>
                    <th style="text-align: center;">Awal</th>
                    <th style="text-align: center;">Akhir</th>
                    <th style="text-align: center;">Pemakaian</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usagesGroup->sortBy([['installation.rt', 'asc'], ['tgl_akhir', 'asc']]) as $i => $usage)
                    @php
                        $dendaPemakaianLalu = 0;

                        // ambil bulan sebelumnya dari pemakaian sekarang
                        $bulanLalu = date('Y-m', strtotime('-1 month', strtotime($usage->tgl_pemakaian)));

                        $usageLalu = \App\Models\Usage::where('id_instalasi', $usage->id_instalasi)
                            ->where('tgl_pemakaian', 'LIKE', $bulanLalu . '%')
                            ->first();

                        if ($usageLalu) {
                            // jika bulan lalu belum bayar dan sudah lewat jatuh tempo
                            if ($usageLalu->status == 'UNPAID' && date('Y-m-d') > $usageLalu->tgl_akhir) {
                                $dendaPemakaianLalu = $trx_settings->denda ?? 0;
                            }
                        }

                        $abodemen = $trx_settings->abodemen ?? 0;
                        $total = $usage->nominal + $abodemen + $dendaPemakaianLalu;

                        // Hitung jumlah status Paid dan Unpaid
                        if (strtoupper($usage->status) == 'PAID') {
                            $totalPaid++;
                        } elseif (strtoupper($usage->status) == 'UNPAID') {
                            $totalUnpaid++;
                        }
                    @endphp
                    <tr>
                        <td align="center">{{ $i + 1 }}</td>
                        <td>{{ $usage->customers->nama }}</td>
                        <td>{{ $usage->installation->kode_instalasi }}{{ $usage->installation->package?->inisial ? '-' . $usage->installation->package->inisial : '' }}
                        </td>
                        <td align="center">{{ $usage->installation->rt ?? '00' }}</td>
                        <td align="center">{{ $usage->awal }}</td>
                        <td align="center">{{ $usage->akhir }}</td>
                        <td align="center">{{ $usage->jumlah }}</td>
                        <td align="center">{{ $usage->status }}</td>
                        <td align="right"><b>{{ number_format($total, 2, ',', '.') }}</b></td>
                    </tr>
                    @php
                        $subtotal += $total;
                        $totalPemakaian += $usage->jumlah;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" align="center"><b>Total</b></td>
                    <td align="center"><b>{{ $totalPemakaian }}</b></td>
                    <td style="padding: 0; vertical-align: middle;">
                        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 12px;">
                            <tr>
                                <td style="border: none; padding: 2px 4px;">Paid</td>
                                <td style="border: none; padding: 2px 4px; width: 4px;">:</td>
                                <td style="border: none; padding: 2px 4px; text-align: left;">
                                    <b>{{ $totalPaid }}</b></td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 4px; border-top: 1px solid #000;">Unpaid</td>
                                <td style="border: none; padding: 2px 4px; border-top: 1px solid #000; width: 4px;">:
                                </td>
                                <td
                                    style="border: none; padding: 2px 4px; border-top: 1px solid #000; text-align: left;">
                                    <b>{{ $totalUnpaid }}</b></td>
                            </tr>
                        </table>
                    </td>
                    <td align="right"><b>{{ number_format($subtotal, 2, ',', '.') }}</b></td>
                </tr>
            </tfoot>
        </table>

        @php
            $indexes++;
        @endphp
    @endforeach
</body>

</html>

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Installations;
use App\Models\Settings;
use App\Models\Usage;
use App\Models\User;
use App\Services\UsageServices;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UsageController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $tahun = request()->get('tahun') ?: date('m');
            $bulan = request()->get('bulan') ?: date('m');
            $caterId = request()->get('cater') ?: '';
            $tgl_pakai = date('Y-m', strtotime($tahun . '-' . $bulan . '-01'));

            $rekening_denda = Account::where([
                ['kode_akun', '4.1.01.04'],
                ['business_id', Session::get('business_id')],
            ])->first();

            $pengaturan = Settings::where('business_id', Session::get('business_id'));
            $trx_settings = $pengaturan->first();

            $usages = Usage::where('business_id', Session::get('business_id'))
                ->where('tgl_pemakaian', 'LIKE', $tgl_pakai . '%')
                ->whereHas('installation', function ($q) {
                    $q->where('kategori', 1);
                });

            if ($caterId != '') {
                $usages->where('cater', $caterId);
            }

            $usages = $usages->whereHas('installation', function ($query) {
                $query->whereNotIn('status', ['B', 'C']); // hanya ambil yang bukan B atau C
            })->with([
                'customers',
                'installation',
                'installation.transaction' => function ($query) use ($rekening_denda) {
                    $query->where('rekening_kredit', $rekening_denda->id);
                },
                'installation.village',
                'usersCater',
                'installation.package',
            ])
                ->orderBy('created_at', 'DESC')
                ->get();

            Session::put('usages', $usages);

            return DataTables::of($usages)
                ->addColumn('kode_instalasi_dengan_inisial', function ($usage) {
                    $kode = $usage->installation->kode_instalasi ?? '-';
                    $inisial = $usage->installation->package->inisial ?? '';

                    return $kode . ($inisial ? '-' . $inisial : '');
                })
                ->addColumn('aksi', function ($usage) {
                    $edit = '<a href="/usages/' . $usage->id . '/edit" class="btn btn-warning btn-sm mb-1 mb-md-0 me-md-1"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                    $delete = '<a href="#" data-id="' . $usage->id . '" class="btn btn-danger btn-sm Hapus_pemakaian"><i class="fas fa-trash-alt"></i></a>';

                    return '<div class="d-flex flex-column flex-md-row">' . $edit . $delete . '</div>';
                })
                ->addColumn('tgl_akhir', function ($usage) {
                    return Tanggal::tglIndo($usage->tgl_akhir);
                })
                ->editColumn('nominal', function ($usage) use ($trx_settings) {
                    $tgl_akhhir_lalu = date('Y-m', strtotime('-0 month', strtotime($usage->tgl_akhir)));

                    $dendaPemakaianLalu = 0;
                    if ($usage->installation) {
                        foreach ($usage->installation->transaction as $trx_denda) {
                            if (
                                $trx_denda->tgl_transaksi < $usage->tgl_akhir &&
                                date('Y-m', strtotime($trx_denda->tgl_transaksi)) == $tgl_akhhir_lalu
                            ) {
                                $dendaPemakaianLalu = $trx_denda->total;
                            }
                        }
                    }

                    $nominal = $usage->nominal + $dendaPemakaianLalu + $trx_settings->abodemen;

                    return number_format($nominal, 2);
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
        // Ambil data cater (jabatan 5) untuk dropdown / hidden input
        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '5'],
        ])->get();

        $user = auth()->user(); // atau Session::get('user') kalau pakai session manual

        // Kirim cater_id default untuk user jabatan 5 supaya otomatis filter
        $cater_id = ($user->jabatan == 5) ? $user->id : '';

        $title = 'Data Pemakaian';

        return view('penggunaan.index')->with(compact('title', 'caters', 'user', 'cater_id'));
    }

    public function sampah()
    {
        if (request()->ajax()) {
            $tahun = request()->get('tahun') ?: date('m');
            $bulan = request()->get('bulan') ?: date('m');
            $caterId = request()->get('cater') ?: '';
            $tgl_pakai = date('Y-m', strtotime($tahun . '-' . $bulan . '-01'));

            $rekening_denda = Account::where([
                ['kode_akun', '4.1.01.04'],
                ['business_id', Session::get('business_id')],
            ])->first();

            $pengaturan = Settings::where('business_id', Session::get('business_id'));
            $trx_settings = $pengaturan->first();

            $usages = Usage::where('business_id', Session::get('business_id'))
                ->where('tgl_pemakaian', 'LIKE', $tgl_pakai . '%')
                ->whereHas('installation', function ($q) {
                    $q->where('kategori', 2);
                });

            if ($caterId != '') {
                $usages->where('cater', $caterId);
            }

            $usages = $usages->with([
                'customers',
                'installation',
                'installation.transaction' => function ($query) use ($rekening_denda) {
                    $query->where('rekening_kredit', $rekening_denda->id);
                },
                'installation.village',
                'usersCater',
                'installation.package',
            ])->orderBy('created_at', 'DESC')->get();

            Session::put('usages', $usages);

            return DataTables::of($usages)
                ->addColumn('kode_instalasi_dengan_inisial', function ($usage) {
                    $kode = $usage->installation->kode_instalasi ?? '-';
                    $inisial = $usage->installation->package->inisial ?? '';

                    return $kode . ($inisial ? '-' . $inisial : '');
                })
                ->addColumn('aksi', function ($usage) {
                    $delete = '<a href="#" data-id="' . $usage->id . '" class="btn btn-danger btn-sm Hapus_pemakaian"><i class="fas fa-trash-alt"></i></a>';

                    return '<div class="d-flex flex-column flex-md-row">' . $delete . '</div>';
                })
                ->addColumn('tgl_akhir', function ($usage) {
                    return Tanggal::tglIndo($usage->tgl_akhir);
                })
                ->editColumn('nominal', function ($usage) use ($trx_settings) {
                    $tgl_akhhir_lalu = date('Y-m', strtotime('-0 month', strtotime($usage->tgl_akhir)));

                    $dendaPemakaianLalu = 0;
                    if ($usage->installation) {
                        foreach ($usage->installation->transaction as $trx_denda) {
                            if (
                                $trx_denda->tgl_transaksi < $usage->tgl_akhir &&
                                date('Y-m', strtotime($trx_denda->tgl_transaksi)) == $tgl_akhhir_lalu
                            ) {
                                $dendaPemakaianLalu = $trx_denda->total;
                            }
                        }
                    }

                    $nominal = $usage->nominal + $dendaPemakaianLalu + $trx_settings->abodemen;

                    return number_format($nominal, 2);
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
        // Ambil data cater (jabatan 5) untuk dropdown / hidden input
        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '5'],
        ])->get();

        $user = auth()->user(); // atau Session::get('user') kalau pakai session manual

        // Kirim cater_id default untuk user jabatan 5 supaya otomatis filter
        $cater_id = ($user->jabatan == 5) ? $user->id : '';
        $title = 'Data Pemakaian';

        return view('sampah.index')->with(compact('title', 'caters', 'user', 'cater_id'));
    }

    /**
     * Show cthe form for creating a new resource.
     */
    public function create(Request $request, UsageServices $usageServices)
    {
        return view('penggunaan.create', $usageServices->dataCreate($request->all()));
    }

    public function createSampah(Request $request, UsageServices $usageServices)
    {
        return view('sampah.create', $usageServices->dataCreate($request->all(), 'Data Sampah'));
    }

    public function barcode(Usage $usage)
    {
        $title = '';

        return view('penggunaan.barcode')->with(compact('title'));
    }

    public function store(Request $request)
    {
        $data = $request->only('data')['data'];
        $data['jumlah'] = $data['jumlah'] ?: 0;

        $installation = Installations::where([
            ['business_id', Session::get('business_id')],
            ['id', $data['id']],
        ])->with('package', 'customer')->first();
        $setting = Settings::where('business_id', Session::get('business_id'))->first();

        $result = [];
        $block = json_decode($setting->block, true);
        $harga = json_decode($installation->package->harga, true);
        foreach ($block as $index => $item) {
            preg_match_all('/\d+/', $item['jarak'], $matches);
            $start = (int) $matches[0][0];
            $end = (isset($matches[0][1])) ? $matches[0][1] : 200;

            for ($i = $start; $i <= $end; $i++) {
                $result[$i] = $index;
            }
        }

        $tglPakai = Tanggal::tglNasional($data['tgl_pemakaian']);
        $index_harga = (isset($result[$data['jumlah']])) ? $result[$data['jumlah']] : end($result);
        $tglAkhir = date('Y-m', strtotime('+1 month', strtotime($tglPakai))) . '-' . str_pad($data['toleransi'], 2, '0', STR_PAD_LEFT);

        $insert = [
            'business_id' => Session::get('business_id'),
            'tgl_pemakaian' => Tanggal::tglNasional($data['tgl_pemakaian']),
            'customer' => $data['customer'],
            'awal' => $data['awal'],
            'akhir' => $data['akhir'],
            'jumlah' => $data['akhir'] - $data['awal'],
            'id_instalasi' => $data['id'],
            'kode_instalasi' => $installation->kode_instalasi,
            'tgl_akhir' => $tglAkhir,
            'nominal' => $harga[$index_harga] * ($data['akhir'] - $data['awal']),
            'cater' => $data['id_cater'],
            'user_id' => auth()->user()->id,
        ];

        // Simpan data
        $usage = Usage::create($insert);

        return response()->json([
            'success' => true,
            'msg' => 'Input Pemakain Berhasil ',
            'pemakaian' => $usage,
        ]);
    }

    public function storeSampah(Request $request)
    {
        $data = $request->only('data')['data'];

        $installation = Installations::where([
            ['business_id', Session::get('business_id')],
            ['id', $data['id_instalasi']],
        ])->with('package', 'customer')->first();

        $insert = [
            'business_id' => Session::get('business_id'),
            'tgl_pemakaian' => Tanggal::tglNasional($data['tgl_pemakaian']),
            'customer' => $data['customer'],
            'awal' => 0,
            'akhir' => 0,
            'jumlah' => 0,
            'id_instalasi' => $data['id_instalasi'],
            'kode_instalasi' => $installation->kode_instalasi,
            'tgl_akhir' => Tanggal::tglNasional($data['tgl_pemakaian']),
            'nominal' => 0,
            'cater' => $data['id_cater'],
            'user_id' => auth()->user()->id,
        ];

        // Simpan data
        $usage = Usage::create($insert);

        return response()->json([
            'success' => true,
            'msg' => 'Input Pemakain Berhasil ',
            'pemakaian' => $usage,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function carianggota(Request $request)
    {
        $query = $request->input('query');

        $customer = Customer::where('business_id', Session::get('business_id'))->join('installations', 'customers.id', 'installations.customer_id')
            ->where('customers.nama', 'LIKE', '%' . $query . '%')
            ->orwhere('installations.kode_instalasi', 'LIKE', '%' . $query . '%')->get();

        $data_customer = [];
        foreach ($customer as $cus) {
            $usage = Usage::where('business_id', Session::get('business_id'))->where('id_instalasi', $cus->id)->orderBy('created_at', 'DESC')->first();

            $data_customer[] = [
                'customer' => $cus,
                'usage' => $usage,
            ];
        }

        return response()->json($data_customer);
    }

    public function detailTagihan()
    {
        $keuangan = new Keuangan;

        $usages = Usage::where('business_id', Session::get('business_id'))
            ->where('status', 'UNPAID')
            ->whereHas('usersCater', function ($q) {
                $q->where('jabatan', 5);
            })
            ->with(['customers', 'installation', 'usersCater']) // panggil relasinya
            ->get();

        return [
            'label' => '<i class="fas fa-book"></i> ' . 'Detail Pemakaian Dengan Status <b>(UNPAID)</b>',
            'cetak' => view('penggunaan.partials.DetailTagihan', [
                'usages' => $usages,
            ])->render(),
        ];
    }

    public function cetak(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $keuangan = new Keuangan;
        $id = $request->cetak;

        $rekening_denda = Account::where([
            ['kode_akun', '4.1.01.04'],
            ['business_id', Session::get('business_id')],
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['trx_settings'] = Settings::where('business_id', Session::get('business_id'))->first();

        $usageQuery = Usage::where('business_id', Session::get('business_id'))->whereIn('id', $id);

        if ($request->cater != '') {
            $usageQuery->where('cater', $request->cater);
        }

        if ($request->bulan_tagihan != '') {
            $usageQuery->where('tgl_pemakaian', 'LIKE', '%' . $request->tahun_tagihan . '-' . $request->bulan_tagihan . '%');
        }

        $data['usage'] = $usageQuery->with([
            'customers',
            'installation.village',
            'installation.transaction' => function ($query) use ($rekening_denda) {
                $query->where('rekening_kredit', $rekening_denda->id);
            },
            'usersCater',
            'installation.package',
        ])->get();

        // Pre-fetch all previous month usages in 1 query (avoid N+1)
        $instalasiIds = $data['usage']->pluck('id_instalasi')->unique();
        $bulanMap = [];
        foreach ($data['usage'] as $use) {
            $bulanMap[$use->id_instalasi] = date('Y-m', strtotime('-1 month', strtotime($use->tgl_pemakaian)));
        }

        $prevUsages = collect();
        if (!empty($bulanMap)) {
            $prevUsages = Usage::whereIn('id_instalasi', $instalasiIds)
                ->where(function ($q) use ($bulanMap) {
                    foreach ($bulanMap as $instId => $bln) {
                        $q->orWhere(function ($q2) use ($instId, $bln) {
                            $q2->where('id_instalasi', $instId)->where('tgl_pemakaian', 'LIKE', $bln . '%');
                        });
                    }
                })->get()->keyBy('id_instalasi');
        }

        // Pre-compute denda per usage
        $dendaMap = [];
        $dendaNominal = $data['trx_settings']->denda ?? 0;
        foreach ($data['usage'] as $use) {
            $prev = $prevUsages->get($use->id_instalasi);
            $denda = 0;
            if ($prev && $prev->status == 'UNPAID' && date('Y-m-d') > $prev->tgl_akhir) {
                $denda = $dendaNominal;
            }
            $dendaMap[$use->id] = $denda;
        }
        $data['dendaMap'] = $dendaMap;

        $data['jabatan'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '3'],
        ])->first();

        $data['caters'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '5'],
        ])->get();

        $data['gambar'] = $data['bisnis']->logo;
        $data['keuangan'] = $keuangan;

        // Pre-compute TTD base64 once
        $data['ttdBase64'] = null;
        if (!empty($data['jabatan']->tanda_tangan)) {
            $ttdPath = storage_path('app/public/ttd/' . $data['jabatan']->tanda_tangan);
            if (file_exists($ttdPath)) {
                $ext = pathinfo($ttdPath, PATHINFO_EXTENSION);
                $data['ttdBase64'] = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($ttdPath));
            }
        }

        $view = view('penggunaan.partials.cetak', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('Legal', 'portrait');
        return $pdf->stream();
    }

    public function cetakSampah(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $keuangan = new Keuangan;
        $id = $request->cetak;

        $rekening_denda = Account::where([
            ['kode_akun', '4.1.01.04'],
            ['business_id', Session::get('business_id')],
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['trx_settings'] = Settings::where('business_id', Session::get('business_id'))->first();

        $usageQuery = Usage::where('business_id', Session::get('business_id'))->whereIn('id', $id);

        if ($request->cater != '') {
            $usageQuery->where('cater', $request->cater);
        }

        if ($request->bulan_tagihan != '') {
            $usageQuery->where('tgl_pemakaian', 'LIKE', '%' . $request->tahun_tagihan . '-' . $request->bulan_tagihan . '%');
        }

        $data['usage'] = $usageQuery->with([
            'customers',
            'installation.village',
            'installation.transaction' => function ($query) use ($rekening_denda) {
                $query->where('rekening_kredit', $rekening_denda->id);
            },
            'usersCater',
            'installation.package',
        ])->get();

        $data['jabatan'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '3'],
        ])->first();

        $data['caters'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '5'],
        ])->get();

        $data['gambar'] = $data['bisnis']->logo;
        $data['keuangan'] = $keuangan;

        // Pre-compute TTD base64 once
        $data['ttdBase64'] = null;
        if (!empty($data['jabatan']->tanda_tangan)) {
            $ttdPath = storage_path('app/public/ttd/' . $data['jabatan']->tanda_tangan);
            if (file_exists($ttdPath)) {
                $ext = pathinfo($ttdPath, PATHINFO_EXTENSION);
                $data['ttdBase64'] = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($ttdPath));
            }
        }

        $view = view('Sampah.partials.cetak', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('Legal', 'portrait');
        return $pdf->stream();
    }

    public function cetak_tagihan(Request $request)
    {
        $thn = $request->input('tahun');
        $bln = $request->input('bulan');
        $hari = $request->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $rekening_denda = Account::where([
            ['kode_akun', '4.1.01.04'],
            ['business_id', Session::get('business_id')],
        ])->first();

        $akun_piutang = Account::where([
            ['kode_akun', '1.1.03.01'],
            ['business_id', Session::get('business_id')],
        ])->first();

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => $request->input('cater', null),
            'akun_piutang' => $akun_piutang,
        ];

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();

        // Ambil data usage sesuai filter
        $usagesQuery = Usage::where([
            ['business_id', Session::get('business_id')],
            ['tgl_pemakaian', 'LIKE', $request->tahun_tagihan . '-' . $request->bulan_tagihan . '%'],
        ]);

        if ($request->cater != '') {
            $usagesQuery->where('cater', $request->cater);
        }
        if ($request->has('cetak')) {
            $usagesQuery->whereIn('id', $request->cetak);
        }
        $data['trx_settings'] = Settings::where('business_id', Session::get('business_id'))->first();

        $usages = $usagesQuery->with([
            'customers',
            'installation',
            'installation.village',
            'installation.transaction' => function ($query) use ($rekening_denda) {
                $query->where('rekening_kredit', $rekening_denda->id);
            },
            'transaction' => function ($query) use ($akun_piutang) {
                $query->where('rekening_debit', '!=', $akun_piutang ? $akun_piutang->id : 0);
            },
            'usersCater',
            'installation.package',
        ])->get();

        // Sort
        $data['usages'] = $usages->sortBy([
            fn($a, $b) => strcmp($a->installation->village->dusun, $b->installation->village->dusun),
            fn($a, $b) => $a->installation->rt <=> $b->installation->rt,
            fn($a, $b) => strcmp($a->tgl_akhir, $b->tgl_akhir),
        ]);

        // Ambil nama cater dari relasi Usage → usersCater (jika ada)
        $data['pemakaian_cater'] = $usages->first()?->usersCater?->nama ?? '-';

        $data['title'] = 'Cetak Daftar Tagihan';

        \Carbon\Carbon::setLocale('id');
        $bulan_angka = $request->bulan_tagihan ?? '';
        $data['bulan'] = $bulan_angka
            ? \Carbon\Carbon::create($thn, $bulan_angka, 1)->translatedFormat('F Y')
            : '-';

        $view = view('penggunaan.partials.cetak1', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('F4', 'portrait');

        return $pdf->stream();
    }

    public function tagihan_sampah(Request $request)
    {
        $thn = $request->input('tahun');
        $bln = $request->input('bulan');
        $hari = $request->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $rekening_denda = Account::where([
            ['kode_akun', '4.1.01.04'],
            ['business_id', Session::get('business_id')],
        ])->first();
        $data['trx_settings'] = Settings::where('business_id', Session::get('business_id'))->first();
        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => $request->input('cater', null),
        ];

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();

        $usagesQuery = Usage::where([
            ['business_id', Session::get('business_id')],
            ['tgl_pemakaian', 'LIKE', $request->tahun_tagihan . '-' . $request->bulan_tagihan . '%'],
        ]);

        if ($request->cater != '') {
            $usagesQuery->where('cater', $request->cater);
        }

        $usages = $usagesQuery->with([
            'customers',
            'installation',
            'installation.village',
            'installation.transaction' => function ($query) use ($rekening_denda) {
                $query->where('rekening_kredit', $rekening_denda->id);
            },
            'usersCater',
            'installation.package',
        ])->get();

        $data['usages'] = $usages->sortBy([
            fn($a, $b) => strcmp($a->installation->village->dusun, $b->installation->village->dusun),
            fn($a, $b) => $a->installation->rt <=> $b->installation->rt,
            fn($a, $b) => strcmp($a->tgl_akhir, $b->tgl_akhir),
        ]);

        $data['pemakaian_cater'] = $usages->first()?->usersCater?->nama ?? '-';

        $data['title'] = 'Cetak Daftar Tagihan';

        \Carbon\Carbon::setLocale('id');
        $bulan_angka = $request->bulan_tagihan ?? '';
        $data['bulan'] = $bulan_angka
            ? \Carbon\Carbon::create($thn, $bulan_angka, 1)->translatedFormat('F Y')
            : '-';

        $view = view('sampah.partials.cetak1', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('F4', 'portrait');

        return $pdf->stream();
    }

    public function cetak_input(Request $request)
    {
        $bulan = $request->bulan_tagihan;
        $cater = $request->cater;

        if (strlen($bulan) == 2) {
            $bulan = date('Y') . '-' . $bulan . '-01';
        }

        $bulanCarbon = \Carbon\Carbon::parse($bulan);
        $bulanAwal = $bulanCarbon->copy()->subMonth()->format('Y-m');

        $bisnis = Business::find(Session::get('business_id'));

        $installations = Installations::select('installations.*')
            ->join('customers', 'customers.id', '=', 'installations.customer_id')
            ->where('installations.business_id', Session::get('business_id'))
            ->whereIn('installations.status', ['A', 'B'])
            ->when($cater, fn($q) => $q->where('cater_id', $cater))
            ->with(['customer', 'package', 'village', 'users', 'oneUsage' => fn($q) => $q->where('tgl_pemakaian', 'like', $bulanAwal . '%')])
            ->orderBy('customers.nama', 'ASC') // urut alfabet
            ->orderBy('desa', 'ASC')
            ->orderBy('rt', 'ASC')
            ->get();


        $caterUser = $cater ? User::find($cater) : null;

        $data = [
            'installations' => $installations,
            'bulan' => $bulanCarbon,
            'caterUser' => $caterUser,
            'bisnis' => $bisnis,
            'dusun' => '-', // bisa diganti dengan filter dusun jika ada
            'title' => 'Cetak Form Input',
        ];

        $view = view('penggunaan.partials.cetak2', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper([0, 0, 595.28, 935.43], 'portrait'); // ukuran F4

        return $pdf->stream();
    }

    public function show(Usage $usage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usage $usage)
    {
        $usages = Usage::where('business_id', Session::get('business_id'))->with([
            'customers',
            'installation',
        ])->get();
        $title = 'Data Pemakaian';

        return view('penggunaan.edit')->with(compact('title', 'usage', 'usages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usage $usage)
    {
        $data = $request->only([
            'tgl_akhir',
            'awal',
            'akhir',
            'jumlah',
        ]);

        $rules = [
            'awal' => 'required|numeric',
            'akhir' => 'required|numeric',
            'jumlah' => 'required|numeric',
            'tgl_akhir' => 'required|date_format:d/m/Y',
        ];
        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $installation = Installations::where([
            ['business_id', Session::get('business_id')],
            ['id', $usage->id_instalasi],
        ])->with('package')->first();

        $setting = Settings::where('business_id', Session::get('business_id'))->first();

        $harga = json_decode($installation->package->harga, true);
        $block = json_decode($setting->block, true);

        $result = [];
        foreach ($block as $index => $item) {
            preg_match_all('/\d+/', $item['jarak'], $matches);
            $start = (int) $matches[0][0];
            $end = (isset($matches[0][1])) ? $matches[0][1] : 200;

            for ($i = $start; $i <= $end; $i++) {
                $result[$i] = $index;
            }
        }

        $jumlah = (int) $request->jumlah;
        $index_harga = isset($result[$jumlah]) ? $result[$jumlah] : array_key_last($harga);
        $nominal = $harga[$index_harga] * $jumlah;
        $usage->update([
            'tgl_akhir' => Tanggal::tglNasional($request->tgl_akhir),
            'awal' => $request->awal,
            'akhir' => $request->akhir,
            'jumlah' => $jumlah,
            'nominal' => $nominal,
        ]);

        return redirect('/usages')->with('berhasil', 'Usage berhasil diperbarui!');
    }

    public function destroy(Usage $usage)
    {
        $usage->delete();

        return redirect('/usages')->with('success', 'Pemakaian berhasil dihapus');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Business;
use App\Models\Installations;
use App\Models\Settings;
use App\Models\Usage;
use App\Models\User;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function index()
    {
        $keuangan   = new Keuangan;
        $businessId = Session::get('business_id');
        $today      = date('Y-m-d');

        // ---- Semua angka di sini sekarang adalah COUNT langsung di SQL,
        // ---- tanpa eager-load relasi yang tidak ditampilkan di view.
        $Installation = Installations::where('business_id', $businessId)->count();

        // UsageCount = jumlah instalasi aktif yang punya usage dengan tgl_akhir <= hari ini.
        // Pakai EXISTS (subquery) -> jauh lebih cepat dari load semua + get.
        $UsageCount = Installations::where('business_id', $businessId)
            ->where('status', 'A')
            ->whereExists(function ($q) use ($today) {
                $q->select(DB::raw(1))
                  ->from('usages')
                  ->whereColumn('usages.id_instalasi', 'installations.id')
                  ->where('tgl_akhir', '<=', $today);
            })
            ->count();

        $Tagihan = Usage::where('business_id', $businessId)
            ->where('status', 'UNPAID')
            ->where('tgl_akhir', '<', $today)
            ->count();

        $Tunggakan = Installations::where('business_id', $businessId)
            ->where('status', 'A')
            ->whereHas('usage', function (Builder $query) use ($today) {
                $query->where('status', 'UNPAID')
                      ->where('tgl_akhir', '<=', $today);
            }, '>=', 3)
            ->count();

        $bulan = intval(date('m'));
        $chart = $this->chart(); // sudah di-cache 1 jam

        $pendapatan = $chart['pendapatan'];
        $beban      = $chart['beban'];
        $surplus    = $chart['surplus'];

        $pros_pendapatan = $keuangan->ProsSaldo($pendapatan[$bulan - 1], $pendapatan[$bulan]);
        $pros_beban      = $keuangan->ProsSaldo($beban[$bulan - 1], $beban[$bulan]);
        $pros_surplus    = $keuangan->ProsSaldo($surplus[$bulan - 1], $surplus[$bulan]);

        $charts = json_encode($chart);

        $title    = 'Dashboard';
        $api      = env('APP_API', 'http://localhost:8080');
        $business = Business::where('id', $businessId)->first();

        return view('welcome')->with(compact(
            'Installation', 'Tunggakan', 'UsageCount', 'Tagihan',
            'title', 'charts', 'pendapatan', 'beban', 'surplus',
            'pros_pendapatan', 'pros_beban', 'pros_surplus',
            'business', 'api'
        ));
    }

    public function usagesDasboard(Request $request)
    {
        $cater_id = $request->query('cater_id');
        $business_id = Session::get('business_id');

        $air = Installations::where('kategori', 1)
            ->where('cater_id', $cater_id)
            ->where('status', 'A')
            ->where('business_id', $business_id)
            ->count();

        $sampah = Installations::where('kategori', 2)
            ->where('cater_id', $cater_id)
            ->where('status', 'A')
            ->where('business_id', $business_id)
            ->count();

        return view('partialsDashboard.usages', compact('air', 'sampah'));
    }

    public function dataInstallations(Request $request)
    {
        $type = $request->type;
        $cater_id = $request->cater_id;
        $business_id = session('business_id');

        $kategori = $type == 'air' ? 1 : 2;

        $aktif = Installations::with(['customer', 'package'])
            ->where('kategori', $kategori)
            ->where('status', 'A')
            ->where('cater_id', $cater_id)
            ->where('business_id', $business_id)
            ->get();

        return response()->json([
            'Aktif' => $aktif
        ]);
    }

    public function installations()
    {
        $Permohonan = Installations::where('business_id', Session::get('business_id'))->where('status', '0')->orwhere('status', 'R')->with([
            'customer',
            'package'
        ])->get();
        $Pasang = Installations::where('business_id', Session::get('business_id'))->where('status', 'I')->with([
            'customer',
            'package'
        ])->get();
        $Aktif = Installations::where('business_id', Session::get('business_id'))->where('status', 'A')->with([
            'customer',
            'package'
        ])->get();

        return response()->json([
            'Permohonan' => $Permohonan,
            'Pasang' => $Pasang,
            'Aktif' => $Aktif
        ]);
    }

    public function usages()
    {
        $Usages = Installations::where('business_id', Session::get('business_id'))->where('status', 'A')->with([
            'customer',
            'package',
            'oneUsage' => function ($query) {
                $query->where('tgl_akhir', '<=', date('Y-m-d'));
            }
        ])->get();

        return response()->json([
            'Usages' => $Usages
        ]);
    }

    public function tunggakan()
    {
        $today = date('Y-m-d');

        // Pakai subquery agregat untuk jumlah_tunggakan -> 1 query tambahan,
        // tidak load semua baris Usage.
        $sub = DB::table('usages')
            ->select('id_instalasi', DB::raw('COUNT(*) AS jml'))
            ->where('status', 'UNPAID')
            ->whereDate('tgl_akhir', '<=', $today)
            ->groupBy('id_instalasi');

        $tunggakan = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->whereHas('usage', function (Builder $query) use ($today) {
                $query->where('status', 'UNPAID')
                    ->whereDate('tgl_akhir', '<=', $today);
            })
            ->with(['customer', 'package'])
            ->leftJoinSub($sub, 't', fn ($j) => $j->on('t.id_instalasi', '=', 'installations.id'))
            ->addSelect('installations.*', 't.jml as jumlah_tunggakan')
            ->get();

        return response()->json([
            'tunggakan' => $tunggakan
        ]);
    }



    public function tagihan()
    {
        $tgl_akhir = request()->get('tgl_akhir') ?: date('Y-m-d');
        $Tagihan = Usage::where('business_id', Session::get('business_id'))->where([
            ['status', 'UNPAID'],
            ['tgl_akhir', '<', $tgl_akhir]
        ])->with([
            'installation',
            'installation.customer',
            'installation.customer.village',
            'installation.package'
        ])->get();
        $setting = Settings::where('business_id', Session::get('business_id'))->first();

        $result = [];
        $block = json_decode($setting->block, true);
        foreach ($block as $index => $item) {
            preg_match_all('/\d+/', $item['jarak'], $matches);
            $start = (int)$matches[0][0];
            $end = (int)$matches[0][1];

            for ($i = $start; $i <= $end; $i++) {
                $result[$i] = $index;
            }
        }

        return response()->json([
            'Tagihan' => $Tagihan,
            'setting' => $setting,
            'block' => $result
        ]);
    }

    public function sps($id)
    {
        $keuangan = new Keuangan;

        $thn = request()->input('tahun');
        $bln = request()->input('bulan');
        $hari = request()->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => request()->input('cater', null),
        ];

        if (request()->input('bulanan')) {
            $data['bulanan'] = true;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->first();

        $data['ket'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '8']
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['tunggakan'] = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->where('id', $id)
            ->whereHas('usage', function ($query) {
                $query->where('status', 'UNPAID')
                    ->whereDate('tgl_akhir', '<=', date('Y-m-d'));
            }, '>=', 2)
            ->with([
                'customer',
                'settings',
                'package',
                'usage' => function ($query) {
                    $query->where('status', 'UNPAID')
                        ->whereDate('tgl_akhir', '<=', date('Y-m-d'));
                }
            ])
            ->first();


        $data['keuangan'] = $keuangan;
        $data['title'] = 'Cetak Tunggakan (SPS)';

        return view('partialsDashboard.sps', $data);
    }
    public function Cetaktunggakan2($id)
    {
        $keuangan = new Keuangan;

        $thn = request()->input('tahun');
        $bln = request()->input('bulan');
        $hari = request()->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => request()->input('cater', null),
        ];

        if (request()->input('bulanan')) {
            $data['bulanan'] = true;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->first();

        $data['ket'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '8']
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['tunggakan'] = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->where('id', $id)
            ->whereHas('usage', function ($query) {
                $query->where([
                    ['status', 'UNPAID'],
                    ['tgl_akhir', '<=', date('Y-m-d')],
                ]);
            })
            ->with([
                'customer',
                'settings',
                'package',
                'usage' => function ($query) {
                    $query->where([
                        ['status', 'UNPAID'],
                        ['tgl_akhir', '<=', date('Y-m-d')],
                    ])
                        ->orderBy('tgl_akhir', 'asc') // ambil tgl_akhir paling awal
                        ->limit(2); // hanya 1 data
                }
            ])
            ->first();


        $data['keuangan'] = $keuangan;
        $data['title'] = 'Cetak Tunggakan (SP)';

        return view('partialsDashboard.tunggakan2', $data);
    }
    public function Cetaktunggakan1($id)
    {
        $keuangan = new Keuangan;

        $thn = request()->input('tahun');
        $bln = request()->input('bulan');
        $hari = request()->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => request()->input('cater', null),
        ];

        if (request()->input('bulanan')) {
            $data['bulanan'] = true;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->first();

        $data['ket'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '8']
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['tunggakan'] = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->where('id', $id)
            ->whereHas('usage', function ($query) {
                $query->where([
                    ['status', 'UNPAID'],
                    ['tgl_akhir', '<=', date('Y-m-d')],
                ]);
            })
            ->with([
                'customer',
                'settings',
                'package',
                'usage' => function ($query) {
                    $query->where([
                        ['status', 'UNPAID'],
                        ['tgl_akhir', '<=', date('Y-m-d')],
                    ])
                        ->orderBy('tgl_akhir', 'asc')
                        ->limit(1);
                }
            ])
            ->first();


        $data['keuangan'] = $keuangan;
        $data['title'] = 'Cetak Tunggakan (ST)';

        return view('partialsDashboard.tunggakan1', $data);
    }

    private function chart()
    {
        $businessId = Session::get('business_id');
        $tahun      = (int) date('Y');
        $bulanNow   = (int) date('m');

        $cacheKey = "dashboard.chart.{$businessId}.{$tahun}.{$bulanNow}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($businessId, $tahun, $bulanNow) {

            // Agregasi langsung di MySQL -> tidak load semua baris Amount ke PHP.
            // CASE jenis_mutasi untuk meniru logika debit/kredit di kode lama.
            $rows = DB::table('accounts')
                ->join('amounts', 'amounts.account_id', '=', 'accounts.id')
                ->where('accounts.business_id', $businessId)
                ->whereIn('accounts.lev1', ['4', '5'])
                ->where('amounts.tahun', $tahun)
                ->where('amounts.bulan', '<=', $bulanNow)
                ->groupBy('accounts.lev1', 'amounts.bulan')
                ->selectRaw('accounts.lev1 as lev1, amounts.bulan as bulan,
                    SUM(CASE WHEN accounts.jenis_mutasi = "kredit"
                             THEN amounts.kredit - amounts.debit
                             ELSE amounts.debit - amounts.kredit END) as saldo')
                ->get();

            $bulan = [];
            for ($i = 0; $i <= $bulanNow; $i++) {
                $bulan[$i] = ['pendapatan' => 0, 'beban' => 0];
            }

            foreach ($rows as $r) {
                $b = (int) $r->bulan;
                if (!isset($bulan[$b])) continue;
                if ((string) $r->lev1 === '4') {
                    $bulan[$b]['pendapatan'] += (float) $r->saldo;
                } else {
                    $bulan[$b]['beban']      += (float) $r->saldo;
                }
            }

            $nama_bulan = [];
            $pendapatan = [];
            $beban      = [];
            $surplus    = [];

            foreach ($bulan as $key => $value) {
                $saldo_pendapatan = 0;
                $saldo_beban      = 0;
                if ($key > 0) {
                    $saldo_pendapatan = $value['pendapatan'] - $bulan[$key - 1]['pendapatan'];
                    $saldo_beban      = $value['beban']      - $bulan[$key - 1]['beban'];
                }

                $pendapatan[$key] = $saldo_pendapatan;
                $beban[$key]      = $saldo_beban;
                $surplus[$key]    = $saldo_pendapatan - $saldo_beban;

                if ($key === 0) {
                    $nama_bulan[$key] = 'Awal Tahun';
                } else {
                    $tanggal = date('Y-m-d', strtotime($tahun . '-' . $key . '-01'));
                    $nama_bulan[$key] = Tanggal::namaBulan($tanggal);
                }
            }

            return [
                'nama_bulan' => $nama_bulan,
                'pendapatan' => $pendapatan,
                'beban'      => $beban,
                'surplus'    => $surplus,
            ];
        });
    }
}

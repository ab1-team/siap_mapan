<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Family;
use App\Models\Installations;
use App\Models\Package;
use App\Models\Region;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\Usage;
use App\Models\Account;
use App\Models\Business;
use App\Models\AkunLevel1;
use App\Models\AkunLevel2;
use App\Models\AkunLevel3;
use App\Models\Amount;
use App\Models\Village;
use App\Models\Ebudgeting;
use App\Models\User;
use App\Models\JenisTransactions;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\Tanggal;
use App\Utils\Inventaris as UtilsInventaris;
use App\Utils\Keuangan;
use DB;
use PDF;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect('/transactions/jurnal_umum');
    }
    //tampil jurnal umum
    public function jurnal_umum()
    {
        $businessId = Session::get('business_id');

        try {
            $transactions = $businessId
                ? Transaction::where('business_id', $businessId)->orderBy('id', 'desc')->limit(200)->get()
                : collect();
        } catch (\Throwable $e) {
            $transactions = collect();
        }

        try {
            $jenis_transaksi = JenisTransactions::all();
        } catch (\Throwable $e) {
            $jenis_transaksi = collect();
        }

        try {
            $rekening = Account::where('business_id', $businessId);
            $business = $businessId ? Business::where('id', $businessId)->first() : null;
        } catch (\Throwable $e) {
            $rekening = Account::where('business_id', 0);
            $business = null;
        }

        $title = ' Transaksi';
        return view('transaksi.jurnal_umum.index')->with(compact('title', 'business', 'rekening', 'transactions', 'jenis_transaksi'));
    }
    //tampil pelunasan instalasi
    public function pelunasan_instalasi()
    {
        $businessId = Session::get('business_id');

        try {
            $setting = $businessId ? Settings::where('business_id', $businessId)->first() : null;
        } catch (\Throwable $e) {
            $setting = null;
        }

        try {
            $installations = Installations::where('business_id', $businessId);
        } catch (\Throwable $e) {
            $installations = Installations::where('business_id', 0);
        }

        try {
            $status_0 = $businessId
                ? Installations::where('business_id', $businessId)->where('status', '0')->with(
                    'customer',
                    'village',
                    'package'
                )->get()
                : collect();
        } catch (\Throwable $e) {
            $status_0 = collect();
        }

        $title = 'Pelunasan Instalasi';
        return view('transaksi.pelunasan_instalasi')->with(compact('title', 'setting', 'status_0'));
    }
    //tampil tagihan_bulanan
    public function tagihan_bulanan()
    {
        try {
            $businessId = Session::get('business_id');
            $transactions = collect();
            $status_0 = collect();

            if ($businessId) {
                try {
                    $transactions = Transaction::where('business_id', $businessId)
                        ->orderBy('id', 'desc')
                        ->limit(200)
                        ->get();
                } catch (\Throwable $e) {
                    $transactions = collect();
                }

                try {
                    $status_0 = Installations::where('business_id', $businessId)
                        ->where('status', '0')
                        ->with(['customer', 'village', 'package'])
                        ->get();
                } catch (\Throwable $e) {
                    $status_0 = collect();
                }
            }
        } catch (\Throwable $e) {
            $transactions = collect();
            $status_0 = collect();
        }

        $title = 'Pelunasan Tagihan Bulanan';
        return view('transaksi.tagihan_bulanan')->with(compact('title', 'transactions', 'status_0'));
    }

    public function komisi_sps()
    {
        $akunKas = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'like', '1.1.01%')->get();
        $akunFeeKolektor = Account::where('business_id', Session::get('business_id'))->where('kode_akun', '2.1.02.02')->first();

        $commissionTransactions = Transaction::where('business_id', Session::get('business_id'))
            ->where('rekening_kredit', $akunFeeKolektor->id)
            ->where('tgl_transaksi', '<=', date('Y-m-d'))
            ->with([
                'Installations',
                'Installations.village',
                'Installations.customer',
                'Usage',
                'transaction' => function ($query) use ($akunFeeKolektor) {
                    $query->where('rekening_debit', $akunFeeKolektor->id)->where('tgl_transaksi', '<=', date('Y-m-d'));
                }
            ])->get();

        $title = 'Transaksi Komisi SPS';
        return view('transaksi.komisi_sps')->with(compact('title', 'commissionTransactions', 'akunKas', 'akunFeeKolektor'));
    }

    //tampil rekening jurnal umum
    public function rekening($id)
    {
        $jenis_transaksi = JenisTransactions::where('id', $id)->firstOrFail();

        $label1 = 'Pilih Sumber Dana';
        $tahun = request()->get('tahun', date('Y'));
        $bulan = request()->get('bulan', date('m'));
        $tgl_kondisi = date('Y-m-t', strtotime($tahun . '-' . $bulan . '-01'));

        if ($id == 1) {
            $rek1 = Account::where('business_id', Session::get('business_id'))->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('lev1', '2')->orWhere('lev1', '3')->orWhere('lev1', '4');
                })->where([
                    ['kode_akun', '!=', '2.1.04.01'],
                    ['kode_akun', '!=', '2.1.04.02'],
                    ['kode_akun', '!=', '2.1.04.03'],
                    ['kode_akun', '!=', '2.1.02.01'],
                    ['kode_akun', '!=', '2.1.03.01'],
                    ['kode_akun', 'NOT LIKE', '4.1.01%'],
                ]);
            })->orderBy('kode_akun', 'ASC')->get();

            $rek2 = Account::where('business_id', Session::get('business_id'))->where('lev1', '1')->orderBy('kode_akun', 'ASC')->get();

            $label2 = 'Disimpan Ke';
        } elseif ($id == 2) {
            $rek1 = Account::where('business_id', Session::get('business_id'))->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('lev1', '1')->orWhere('lev1', '2');
                })->where([
                    ['kode_akun', 'NOT LIKE', '2.1.04%'],
                ]);
            })->where(function ($query) use ($tgl_kondisi) {
                $query->whereNull('tgl_nonaktif')->orWhere('tgl_nonaktif', '>', $tgl_kondisi);
            })->orderBy('kode_akun', 'ASC')->get();

            $rek2 = Account::where('business_id', Session::get('business_id'))->where('lev1', '2')->orWhere('lev1', '3')->orWhere('lev1', '5')->orderBy('kode_akun', 'ASC')->get();

            $label2 = 'Keperluan';
        } elseif ($id == 3) {
            $rek1 = Account::where('business_id', Session::get('business_id'))->whereNull('tgl_nonaktif')->orWhere('tgl_nonaktif', '>', $tgl_kondisi)->get();

            $rek2 = Account::where('business_id', Session::get('business_id'))->whereNull('tgl_nonaktif')->orWhere('tgl_nonaktif', '>', $tgl_kondisi)->get();

            $label2 = 'Disimpan Ke';
        }

        return view('transaksi.jurnal_umum.partials.rekening', compact('rek1', 'rek2', 'label1', 'label2'));
    }
    //tampil form rekening jurnal umum
    public function form()
    {
        $keuangan = new Keuangan;
        $tgl_transaksi = Tanggal::tglNasional(request()->get('tgl_transaksi'));
        $jenis_transaksi = request()->get('jenis_transaksi');

        $sumber_dana_id = request()->get('sumber_dana');
        $disimpan_ke_id = request()->get('disimpan_ke');

        // Ambil kode_akun dari tabel Account berdasarkan id
        $sumber_dana = Account::where('business_id', Session::get('business_id'))->where('id', $sumber_dana_id)->first();
        $disimpan_ke = Account::where('business_id', Session::get('business_id'))->where('id', $disimpan_ke_id)->first();

        $kuitansi = false;
        $relasi = false;
        $files = 'bm';
        if (Keuangan::startWith($disimpan_ke->kode_akun, '1.1.01') && !Keuangan::startWith($sumber_dana->kode_akun, '1.1.01')) {
            $file = "c_bkm";
            $files = "BKM";
            $kuitansi = true;
            $relasi = true;
        } elseif (!Keuangan::startWith($disimpan_ke->kode_akun, '1.1.01') && Keuangan::startWith($sumber_dana->kode_akun, '1.1.01')) {
            $file = "c_bkk";
            $files = "BKK";
            $kuitansi = true;
            $relasi = true;
        } elseif (Keuangan::startWith($disimpan_ke->kode_akun, '1.1.01') && Keuangan::startWith($sumber_dana->kode_akun, '1.1.01')) {
            $file = "c_bm";
            $files = "BM";
        } elseif (Keuangan::startWith($disimpan_ke->kode_akun, '1.1.02') && !(Keuangan::startWith($sumber_dana->kode_akun, '1.1.01') || Keuangan::startWith($sumber_dana->kode_akun, '1.1.02'))) {
            $file = "c_bkm";
            $files = "BKM";
            $kuitansi = true;
            $relasi = true;
        } elseif (Keuangan::startWith($disimpan_ke->kode_akun, '1.1.02') && Keuangan::startWith($sumber_dana->kode_akun, '1.1.02')) {
            $file = "c_bm";
            $files = "BM";
        } elseif (Keuangan::startWith($disimpan_ke->kode_akun, '5.') && !(Keuangan::startWith($sumber_dana->kode_akun, '1.1.01') || Keuangan::startWith($sumber_dana->kode_akun, '1.1.02'))) {
            $file = "c_bm";
            $files = "BM";
        } elseif (!(Keuangan::startWith($disimpan_ke->kode_akun, '1.1.01') || Keuangan::startWith($disimpan_ke->kode_akun, '1.1.02')) && Keuangan::startWith($sumber_dana->kode_akun, '1.1.02')) {
            $file = "c_bm";
            $files = "BM";
        } elseif (!(Keuangan::startWith($disimpan_ke->kode_akun, '1.1.01') || Keuangan::startWith($disimpan_ke->kode_akun, '1.1.02')) && Keuangan::startWith($sumber_dana->kode_akun, '4.')) {
            $file = "c_bm";
            $files = "BM";
        }

        if (Keuangan::startWith($sumber_dana->kode_akun, '1.2.01') && Keuangan::startWith($disimpan_ke->kode_akun, '5.3.02.01') && $jenis_transaksi == 2) {
            $kode = explode('.', $sumber_dana->kode_akun);
            $jenis = intval($kode[2]);
            $kategori = intval($kode[3]);

            $inventaris = Inventory::where([
                ['jenis', $jenis],
                ['kategori', $kategori]
            ])->whereNotNull('tgl_beli')->where(function ($query) {
                $query->where('status', 'Baik')->orwhere('status', 'Rusak');
            })->get();
            return view('transaksi.jurnal_umum.partials.form_hapus_inventaris')->with(compact('inventaris', 'tgl_transaksi'));
        } else {
            if (Keuangan::startWith($disimpan_ke->kode_akun, '1.2.01') || Keuangan::startWith($disimpan_ke->kode_akun, '1.2.03')) {
                return view('transaksi.jurnal_umum.partials.form_inventaris')->with(compact('relasi'));
            } else {
                if (Keuangan::startWith($sumber_dana->kode_akun, '1.1.01') && Keuangan::startWith($disimpan_ke->kode_akun, '2.1.02.02')) {
                    $akunFeeKolektor = Account::where('business_id', Session::get('business_id'))->where('kode_akun', '2.1.02.02')->first();

                    $commissionTransactions = Transaction::where('business_id', Session::get('business_id'))
                        ->where('rekening_kredit', $akunFeeKolektor->id)
                        ->where('tgl_transaksi', '<=', $tgl_transaksi)
                        ->with([
                            'Installations',
                            'Installations.village',
                            'Installations.customer',
                            'Usages',
                            'transaction' => function ($query) use ($akunFeeKolektor, $tgl_transaksi) {
                                $query->where('rekening_debit', $akunFeeKolektor->id)->where('tgl_transaksi', '<=', $tgl_transaksi);
                            }
                        ])->get();
                    return view('transaksi.jurnal_umum.partials.form_komisi')->with(compact('commissionTransactions'));
                } else {
                    $keterangan_transaksi = '';
                    if ($jenis_transaksi == 1) {
                        if (!empty($disimpan_ke)) {
                            $keterangan_transaksi = "Dari " . $sumber_dana->nama_akun . " ke " . $disimpan_ke->nama_akun;
                        }
                    } else if ($jenis_transaksi == 2) {
                        if (!empty($disimpan_ke)) {
                            $keterangan_transaksi = $disimpan_ke->nama_akun;
                            $kd = substr($sumber_dana->kode_akun, 0, 6);
                            if ($kd == '1.1.01') {
                                $keterangan_transaksi = "Bayar " . $disimpan_ke->nama_akun;
                            }
                            if ($kd == '1.1.02') {
                                $keterangan_transaksi = "Transfer " . $disimpan_ke->nama_akun;
                            }
                        }
                    } else if ($jenis_transaksi == 3) {
                        if (!empty($disimpan_ke)) {
                            $keterangan_transaksi = "Pemindahan Saldo " . $sumber_dana->nama_akun . " ke " . $disimpan_ke->nama_akun;
                        }
                    }

                    $susut = 0;
                    return view('transaksi.jurnal_umum.partials.form_nominal')->with(compact('relasi', 'keterangan_transaksi', 'susut'));
                }
            }
        }
    }

    public function ebudgeting()
    {
        $business = Business::where('id', Session::get('business_id'))->first();

        $title = ' E-Budgeting';
        return view('transaksi.ebudgeting.index')->with(compact('title', 'business'));
    }

    public function anggaran(Request $request)
    {
        $data = $request->only(['tahun', 'bulan']);

        $validate = Validator::make($data, [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $cek = Ebudgeting::where([
            ['tahun', $request->tahun],
            ['bulan', $request->bulan]
        ])->orderBy('bulan', 'ASC')->orderBy('kode_akun', 'ASC');

        $jumlah = $cek->count();

        $tgl_kondisi = date('Y-m-t', strtotime($request->tahun . '-' . $request->bulan . '-01'));

        if ($jumlah > 0) {
            $akun1 = AkunLevel1::where('lev1', '>=', '4')->with([
                'akun2',
                'akun2.akun3',
                'akun2.akun3.accounts' => function ($query) use ($tgl_kondisi) {
                    $query->where('business_id', Session::get('business_id'))
                        ->where(function ($q) use ($tgl_kondisi) {
                            $q->whereNull('tgl_nonaktif')->orWhere('tgl_nonaktif', '>', $tgl_kondisi);
                        });
                },
                'akun2.akun3.accounts.amount.eb' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', $data['bulan']]
                    ]);
                },
            ])->orderBy('kode_akun', 'ASC')->get();
        } else {
            $akun1 = AkunLevel1::where('lev1', '>=', '4')
                ->with([
                    'akun2',
                    'akun2.akun3',
                    'akun2.akun3.accounts' => function ($query) use ($tgl_kondisi) {
                        $query->where('business_id', Session::get('business_id'))
                            ->where(function ($q) use ($tgl_kondisi) {
                                $q->whereNull('tgl_nonaktif')->orWhere('tgl_nonaktif', '>', $tgl_kondisi);
                            });
                    },
                    'akun2.akun3.accounts.amount.eb' => function ($query) use ($data) {
                        $query->where([
                            ['tahun', $data['tahun']],
                            ['bulan', $data['bulan'] - 1]
                        ]);
                    },
                ])
                ->orderBy('kode_akun', 'ASC')
                ->get();
        }
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        return response()->json([
            'success' => true,
            'view' => view('transaksi.ebudgeting.create')->with(compact('akun1', 'jumlah', 'tahun', 'bulan'))->render()
        ]);
    }

    public function simpananggaran(Request $request)
    {
        $data = $request->only(['tahun', 'bulan', 'jumlah']);
        $validate = Validator::make($data, [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }
        $insert = [];
        foreach ($request->jumlah as $id => $nominal) {
            $insert[] = [
                'account_id' => $id,
                'tahun' => $request->tahun,
                'bulan' => $request->bulan,
                'jumlah' => floatval(str_replace(',', '', $nominal))
            ];
        }
        Ebudgeting::where([
            ['tahun', $request->tahun],
            ['bulan', $request->bulan],
        ])->delete();
        Ebudgeting::insert($insert);

        $nama_bulan = Tanggal::namaBulan($request->tahun . '-' . $request->bulan . '-01');

        return response()->json([
            'success' => true,
            'msg' => 'Rencana Anggaran bulan ' . $nama_bulan . ' berhasil disimpan.'
        ]);
    }


    public function jurnalTutupBuku()
    {
        $business = Business::where('id', Session::get('business_id'))->first();
        $title = 'Tutup Buku';
        return view('transaksi.tutup_buku.index')->with(compact('title', 'business'));
    }

    public function tutup_buku(Request $request)
    {

        $keuangan = new Keuangan;

        $tahun = $request->tahun;
        $bulan = date('m');
        if ($tahun < date('Y')) {
            $bulan = 12;
        }
        $tgl_kondisi = $tahun . '-' . $bulan . '-' . date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $surplus = $keuangan->laba_rugi($tgl_kondisi);

        $success = false;
        $migrasi_saldo = false;
        if ($request->pembagian_laba == 'false') {
            $jumlah_riwayat = $request->jumlah_riwayat;
            $total_riwayat = $request->total_riwayat;

            if ($jumlah_riwayat < $total_riwayat) {
                $migrasi_saldo = true;
            }

            $tahun_tb = $tahun + 1;
            $kode_rekening = Account::where(function ($query) use ($tgl_kondisi) {
                $query->whereNull('tgl_nonaktif')->orwhere('tgl_nonaktif', '>', $tgl_kondisi);
            })->where('business_id', Session::get('business_id'))->with([
                'amount' => function ($query) use ($tahun, $bulan) {
                    $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                        $query->where('bulan', '0')->orwhere('bulan', $bulan);
                    });
                }
            ])->get();

            $data_id = [];
            $saldo_tutup_buku = [];
            foreach ($kode_rekening as $rek) {
                $saldo_awal_debit = 0;
                $saldo_awal_kredit = 0;
                $debit = 0;
                $kredit = 0;

                $bulan_tb = '0';
                if ($rek->lev1 >= 4) {
                    foreach ($rek->amount as $saldo) {
                        if ($saldo->bulan == 0) {
                            if ($saldo->debit != 0) $saldo_awal_debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $saldo_awal_kredit = floatval($saldo->kredit);
                        } else {
                            if ($saldo->debit != 0) $debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $kredit = floatval($saldo->kredit);
                        }
                    }

                    $saldo_debit = $debit;
                    $saldo_kredit = $kredit;

                    $id = str_replace('.', '', $rek->kode_akun) . $tahun . '13';
                    if ($saldo_debit + $saldo_kredit != 0) {
                        $saldo_tutup_buku[] = [
                            'id' => $id,
                            'account_id' => $rek->id,
                            'tahun' => $tahun,
                            'bulan' => 13,
                            'debit' => (string) $saldo_debit,
                            'kredit' => (string) $saldo_kredit
                        ];

                        $data_id[] = $id;
                    }
                }

                $saldo_awal_debit = 0;
                $saldo_awal_kredit = 0;
                $debit = 0;
                $kredit = 0;

                if ($rek->lev1 < 4 && $rek->kode_akun != '3.2.02.01') {
                    foreach ($rek->amount as $saldo) {
                        if ($saldo->bulan == 0) {
                            if ($saldo->debit != 0) $saldo_awal_debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $saldo_awal_kredit = floatval($saldo->kredit);
                        } else {
                            if ($saldo->debit != 0) $debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $kredit = floatval($saldo->kredit);
                        }
                    }
                }

                $saldo_debit = $saldo_awal_debit + $debit;
                $saldo_kredit = $saldo_awal_kredit + $kredit;

                if ($rek->kode_akun == '3.2.01.01') {
                    $saldo_kredit += $surplus;
                }

                $id = str_replace('.', '', $rek->id) . $tahun_tb . "00";
                $saldo_tutup_buku[] = [
                    'id' => $id,
                    'account_id' => $rek->id,
                    'tahun' => $tahun_tb,
                    'bulan' => $bulan_tb,
                    'debit' => (string) $saldo_debit,
                    'kredit' => (string) $saldo_kredit
                ];

                $data_id[] = $id;
            }

            Amount::whereIn('id', $data_id)->delete();
            Amount::insert($saldo_tutup_buku);

            $success = true;
            return redirect('/transactions/tutup_buku')->with('success', 'Tutup Buku Tahun ' . $tahun . ' berhasil.');
        }

        $surplus = $keuangan->laba_rugi($tahun . '-13-00');

        $cadangan_resiko = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'like', '1.1.04.%')->get();
        $pembagian_surplus = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'like', '2.1.%')->where([
            ['nama_akun', 'NOT LIKE', '%pajak%'],
            ['nama_akun', 'NOT LIKE', '%operasional%'],
            ['nama_akun', 'NOT LIKE', '%jangka%'],
            ['nama_akun', 'NOT LIKE', '%bank%'],
            ['nama_akun', 'NOT LIKE', '%ke-3%'],
        ])->get();

        $title = 'Pembagian Laba';
        return view('transaksi.tutup_buku.tutup_buku')->with(compact('title', 'cadangan_resiko', 'pembagian_surplus', 'surplus', 'tgl_kondisi', 'tahun', 'migrasi_saldo', 'success'));
    }

    public function saldo_tutup_buku(Request $request)
    {
        $keuangan = new Keuangan;
        $tgl_pakai = $request->tgl_pakai ?: date('Y-m-d');
        $tahun = $request->tahun;
        $tahun_lalu = $tahun - 1;
        $tahun_pakai = Tanggal::tahun($tgl_pakai);
        $bulan = date('m');
        if ($tahun < date('Y')) {
            $bulan = 12;
        }

        $akun1 = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.accounts' => function ($query) {
                $query->where('business_id', Session::get('business_id'));
            },
            'akun2.akun3.accounts.amount' => function ($query) use ($tahun, $bulan) {
                $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                    $query->where('bulan', '0')->orwhere('bulan', $bulan);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $tgl_kondisi = $tahun . '-' . $bulan . '-' . date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $surplus = $keuangan->laba_rugi($tgl_kondisi);

        $total_riwayat = ($tahun + 1) - $tahun_pakai;
        $jumlah_riwayat = count(Amount::select('tahun')->whereRaw('LENGTH(account_id) = 9')->where('bulan', '0')->whereBetween('tahun', [$tahun_pakai, $tahun])->groupBy('tahun')->get());

        return response()->json([
            'success' => true,
            'view' => view('transaksi.tutup_buku.partials.saldo')->with(compact('akun1', 'surplus', 'tgl_kondisi', 'tahun', 'tahun_lalu', 'total_riwayat', 'jumlah_riwayat'))->render()
        ]);
    }

    public function simpanAlokasiLaba(Request $request)
    {
        $data = $request->only([
            'tgl_kondisi',
            'surplus',
            'total_laba_ditahan',
            'laba_ditahan',
            'surplus_bersih',
            'total_surplus_bersih',
            'total_cadangan_resiko',
            'cadangan_resiko'
        ]);

        $tanggal = $request->tgl_kondisi ?: date('Y-m-d');
        $tahun = Tanggal::tahun($tanggal);
        $tahun_tb = $tahun + 1;
        $bulan = Tanggal::bulan($tanggal);

        $akun_laba_ditahan = Account::where([
            ['business_id', Session::get('business_id')],
            ['kode_akun', '3.2.01.01']
        ])->first();

        $rekening = Account::with([
            'amount' => function ($query) use ($tahun, $bulan) {
                $query->where('tahun', $tahun)
                    ->where(function ($query) use ($bulan) {
                        $query->where('bulan', '0')->orWhere('bulan', $bulan);
                    });
            }
        ])->where('business_id', Session::get('business_id'))->get();


        $alokasi_laba = [
            '3.2.01.01' => 0
        ];

        $laba_ditahan = $data['laba_ditahan']; // Ditambahkan ke 3.2.01.01
        foreach ($laba_ditahan as $key => $val) {
            $value = str_replace(',', '', str_replace('.00', '', $val));
            $alokasi_laba['3.2.01.01'] += floatval($value);
        }

        $surplus_bersih = $data['surplus_bersih'];
        $laba_ditahan = $data['laba_ditahan'];

        $trx = [];
        $data_id = [];
        $saldo_tutup_buku = [];
        $trx['delete'] = [];
        $trx['insert'] = [];

        foreach ($rekening as $rek) {
            $saldo_awal_debit = 0;
            $saldo_awal_kredit = 0;
            $debit = 0;
            $kredit = 0;

            if ($rek->lev1 < 4 && $rek->kode_akun != '3.2.02.01') {
                foreach ($rek->amount as $saldo) {
                    if ($saldo->bulan == 0) {
                        if ($saldo->debit > 0) $saldo_awal_debit = $saldo->debit;
                        if ($saldo->kredit > 0) $saldo_awal_kredit = $saldo->kredit;
                    } else {
                        if ($saldo->debit > 0) $debit = $saldo->debit;
                        if ($saldo->kredit > 0) $kredit = $saldo->kredit;
                    }
                }
            }

            $saldo_debit = floatval($saldo_awal_debit) + floatval($debit);
            $saldo_kredit = floatval($saldo_awal_kredit) + floatval($kredit);
            $id = $rek->id . $tahun_tb . '00';

            if ($rek->kode_akun == '3.2.01.01') {
                $saldo_kredit += floatval($alokasi_laba['3.2.01.01']);
            }

            if (Keuangan::startWith($rek->kode_akun, '2.1.01')) {
                $jumlah = floatval(str_replace(',', '', str_replace('.00', '', $surplus_bersih[$rek->id])));
                $keterangan = trim(str_replace('Utang', '', $rek->nama_akun) . ' tahun ' . $tahun);

                if ($jumlah != 0) {
                    $trx['insert'][] = [
                        'business_id'           => Session::get('business_id'),
                        'tgl_transaksi'         => date('Y-m-d'),
                        'rekening_debit' => $akun_laba_ditahan->id,
                        'rekening_kredit' => $rek->id,
                        'usage_id'              => '0',
                        'installation_id'       => '0',
                        'keterangan'  => $keterangan,
                        'relasi'                => '-',
                        'total'                 => $jumlah,
                        'urutan'                => '0',
                        'user_id'               => auth()->user()->id,
                    ];

                    $saldo_tutup_buku[] = [
                        'id' => $id,
                        'account_id' => $rek->id,
                        'tahun' => $tahun,
                        'bulan' => 13,
                        'debit' => (string) $saldo_kredit,
                        'kredit' => (string) $jumlah
                    ];

                    $alokasi_laba['3.2.01.01'] += floatval(str_replace(',', '', str_replace('.00', '', $surplus_bersih[$rek->id])));
                }

                $trx['delete'][] = $keterangan;
            } else {
                $saldo_tutup_buku[] = [
                    'id' => $id,
                    'account_id' => $rek->id,
                    'tahun' => $tahun_tb,
                    'bulan' => '0',
                    'debit' => (string) $saldo_debit,
                    'kredit' => (string) $saldo_kredit
                ];
            }

            $data_id[] = $id;
        }

        Amount::whereIn('id', $data_id)->delete();
        Amount::insert($saldo_tutup_buku);

        Transaction::whereIn('keterangan', $trx['delete'])->delete();
        Transaction::insert($trx['insert']);

        return response()->json([
            'success' => true,
            'msg' => 'Tutup Buku Tahun ' . $tahun . ' berhasil.'
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = 'Create' . $request->clay;
        return $this->$func($request);
    }

    /**
     *  CreateJurnalUmum.
     */
    private function CreateJurnalUmum($request)
    {
        $keuangan = new Keuangan;

        $tgl_transaksi = Tanggal::tglNasional($request->tgl_transaksi);
        $bisnis = Business::where('id', Session::get('business_id'))->first();
        $sumber_dana_id = request()->get('sumber_dana');
        $disimpan_ke_id = request()->get('disimpan_ke');

        // Ambil kode_akun dari tabel Account berdasarkan id
        $sumber_dana = Account::where('id', $sumber_dana_id)->value('kode_akun');
        $disimpan_ke = Account::where('id', $disimpan_ke_id)->value('kode_akun');

        if (strtotime($tgl_transaksi) < strtotime($bisnis->tgl_pakai)) {
            return response()->json([
                'success' => false,
                'msg' => 'Tanggal transaksi tidak boleh sebelum Tanggal Pakai Aplikasi'
            ]);
        }

        if (Keuangan::startWith($sumber_dana, '1.2.01') && Keuangan::startWith($disimpan_ke, '5.3.02.01') && $request->jenis_transaksi == '2') {
            $data = $request->only([
                'tgl_transaksi',
                'jenis_transaksi',
                'sumber_dana',
                'disimpan_ke',
                'harsat',
                'nama_barang',
                'alasan',
                'unit',
                'harga_jual',
                '_nilai_buku'
            ]);

            $validate = Validator::make($data, [
                'tgl_transaksi'     => 'required',
                'jenis_transaksi'   => 'required',
                'sumber_dana'       => 'required',
                'disimpan_ke'       => 'required',
                'harsat'            => 'required',
                'nama_barang'       => 'required',
                'alasan'            => 'required',
                'unit'              => 'required',
                'harga_jual'        => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
            }

            $sumber_dana = $request->sumber_dana;
            $disimpan_ke = $request->disimpan_ke;
            $nilai_buku = $request->unit * $request->harsat;
            $status = $request->alasan;

            $nama_barang = explode('#', $request->nama_barang);
            $id_inv = $nama_barang[0];
            $jumlah_barang = $nama_barang[1];

            $inv = Inventory::where('id', $id_inv)->first();

            $tgl_beli = $inv->tgl_beli;
            $harsat = $inv->harsat;
            $umur_ekonomis = $inv->umur_ekonomis;
            $sisa_unit = $jumlah_barang - $request->unit;
            $barang = $inv->nama_barang;
            $jenis = $inv->jenis;
            $kategori = $inv->kategori;

            $trx_penghapusan = [
                'business_id'           => Session::get('business_id'),
                'tgl_transaksi'         => (string) Tanggal::tglNasional($request->tgl_transaksi),
                'rekening_debit'        => (string) $request->disimpan_ke,
                'rekening_kredit'       => (string) $request->sumber_dana,
                'usage_id'              => '0',
                'installation_id'       => '0',
                'keterangan'  => (string) 'Penghapusan ' . $request->unit . ' unit ' . $barang . ' (' . $id_inv . ')' . ' karena ' . $status,
                'relasi'                => (string) $request->relasi,
                'total'                 => $nilai_buku,
                'urutan'                => '0',
                'user_id'               => auth()->user()->id,
            ];

            $update_inventaris = [
                'unit'          => $sisa_unit,
                'tgl_validasi'  => Tanggal::tglNasional($request->tgl_transaksi)
            ];

            $update_sts_inventaris = [
                'status'        => ucwords($status),
                'tgl_validasi'  => Tanggal::tglNasional($request->tgl_transaksi)
            ];

            $insert_inventaris = [
                'business_id'   => Session::get('business_id'),
                'nama_barang'   => $barang,
                'tgl_beli'      => $tgl_beli,
                'unit'          => $request->unit,
                'harsat'        => $harsat,
                'umur_ekonomis' => $umur_ekonomis,
                'jenis'         => $jenis,
                'kategori'      => $kategori,
                'status'        => ucwords($status),
                'tgl_validasi'  => Tanggal::tglNasional($request->tgl_transaksi),
            ];

            $rekening_debit = Account::where([
                ['kode_akun', '1.1.01.01'],
                ['business_id', Session::get('business_id')]
            ])->first();
            $rekening_kredit = Account::where([
                ['kode_akun', '4.1.01.03'],
                ['business_id', Session::get('business_id')]
            ])->first();

            $trx_penjualan = [
                'business_id' => Session::get('business_id'),
                'tgl_transaksi'  => (string) Tanggal::tglNasional($request->tgl_transaksi),
                'rekening_debit'  => $rekening_debit->id,
                'rekening_kredit'  => $rekening_kredit->id,
                'usage_id'          => '0',
                'installation_id'     => '0',
                'keterangan_transaksi' => (string) 'Penjualan ' . $request->unit . ' unit ' . $barang . ' (' . $id_inv . ')',
                'relasi'                => (string) $request->relasi,
                'total'                  => str_replace(',', '', str_replace('.00', '', $request->harga_jual)),
                'urutan'                  => '0',
                'user_id'                   => auth()->user()->id,
            ];

            if ($request->unit < $jumlah_barang) {
                if ($status != 'rusak') {
                    $transaksi = Transaction::create($trx_penghapusan);
                }
                Inventory::where('id', $id_inv)->update($update_inventaris);

                if ($status != 'revaluasi') {
                    Inventory::create($insert_inventaris);
                }
            } else {
                if ($status != 'rusak') {
                    $transaksi = Transaction::create($trx_penghapusan);
                }
                Inventory::where('id', $id_inv)->update($update_sts_inventaris);
            }

            if ($status == 'revaluasi') {
                $harga_jual = floatval(str_replace(',', '', str_replace('.00', '', $request->harga_jual)));

                $insert_inventaris_baru = [
                    'business_id'   => Session::get('business_id'),
                    'business_id'   => Session::get('business_id'),
                    'nama_barang'   => $barang,
                    'tgl_beli'      => Tanggal::tglNasional($request->tgl_transaksi),
                    'unit'          => $request->unit,
                    'harsat'        => $harga_jual / $request->unit,
                    'umur_ekonomis' => $umur_ekonomis,
                    'jenis'         => $jenis,
                    'kategori'      => $kategori,
                    'status'        => 'Baik',
                    'tgl_validasi'  => Tanggal::tglNasional($request->tgl_transaksi),
                ];

                if ($harga_jual != $request->_nilai_buku) {
                    $jumlah = $harga_jual - $request->_nilai_buku;
                    $trx_revaluasi = [
                        'business_id' => Session::get('business_id'),
                        'tgl_transaksi' => (string) Tanggal::tglNasional($request->tgl_transaksi),
                        'rekening_debit' => '1',
                        'rekening_kredit' => '61',
                        'usage_id'         => '0',
                        'installation_id'   => '0',
                        'keterangan'         => (string) 'Revaluasi ' . $request->unit . ' unit ' . $barang . ' (' . $id_inv . ')',
                        'relasi'              => '',
                        'total'                => $jumlah,
                        'urutan'                => '0',
                        'user_id'                => auth()->user()->id,
                    ];

                    Transaction::create($trx_revaluasi);
                }

                Transaction::create($insert_inventaris_baru);
            }

            $msg = 'Penghapusan ' . $request->unit . ' unit ' . $barang . ' karena ' . $status;
            if ($status == 'dijual') {
                $transaksi = Transaction::create($trx_penjualan);
                $msg = 'Penjualan ' . $request->unit . ' unit ' . $barang;
            }

            if ($status == 'rusak') {
                return response()->json([
                    'success'   => true,
                    'msg'       => $msg,
                    'view'      => ''
                ]);
            }
        } else {
            if (Keuangan::startWith($disimpan_ke, '1.2.01') || Keuangan::startWith($disimpan_ke, '1.2.03')) {
                $data = $request->only([
                    'tgl_transaksi',
                    'jenis_transaksi',
                    'sumber_dana',
                    'disimpan_ke',
                    'relasi',
                    'nama_barang',
                    'jumlah',
                    'harga_satuan',
                    'umur_ekonomis',
                ]);

                $validate = Validator::make($data, [
                    'tgl_transaksi'     => 'required',
                    'jenis_transaksi'   => 'required',
                    'sumber_dana'       => 'required',
                    'disimpan_ke'       => 'required',
                    'nama_barang'       => 'required',
                    'jumlah'            => 'required',
                    'harga_satuan'      => 'required',
                    'umur_ekonomis'     => 'required'
                ]);

                if ($validate->fails()) {
                    return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
                }

                $rek_simpan = Account::where('business_id', Session::get('business_id'))->where('kode_akun', $disimpan_ke)->first();

                $insert = [
                    'business_id' => Session::get('business_id'),
                    'tgl_transaksi' => (string) Tanggal::tglNasional($request->tgl_transaksi),
                    'rekening_debit' => (string) $request->disimpan_ke,
                    'rekening_kredit' => (string) $request->sumber_dana,
                    'usage_id'         => 0,
                    'installation_id'   => 0,
                    'keterangan'         => (string) '(' . $rek_simpan->nama_akun . ') ' . $request->nama_barang,
                    'relasi'              => (string) $request->relasi,
                    'total'                => str_replace(',', '', str_replace('.00', '', $request->harga_satuan)) * $request->jumlah,
                    'urutan'                => 0,
                    'user_id'                 => auth()->user()->id,
                ];

                $inventaris = [
                    'business_id'   => Session::get('business_id'),
                    'nama_barang'   => $request->nama_barang,
                    'tgl_beli'      => Tanggal::tglNasional($request->tgl_transaksi),
                    'unit'          => $request->jumlah,
                    'harsat'        => str_replace(',', '', str_replace('.00', '', $request->harga_satuan)),
                    'umur_ekonomis' => $request->umur_ekonomis,
                    'jenis'         => str_pad($rek_simpan->lev3, 1, "0", STR_PAD_LEFT),
                    'kategori'      => str_pad($rek_simpan->lev4, 1, "0", STR_PAD_LEFT),
                    'status'        => 'Baik',
                    'tgl_validasi'  => Tanggal::tglNasional($request->tgl_transaksi),
                ];

                $transaksi = Transaction::create($insert);
                $inv = Inventory::create($inventaris);

                $msg = 'Transaksi ' .  $rek_simpan->nama_akun . ' (' . $insert['keterangan'] . ') berhasil disimpan';
            } else {
                $data = $request->only([
                    'tgl_transaksi',
                    'jenis_transaksi',
                    'sumber_dana',
                    'disimpan_ke',
                    'relasi',
                    'keterangan',
                    'nominal'
                ]);

                $validate = Validator::make($data, [
                    'tgl_transaksi'     => 'required',
                    'jenis_transaksi'   => 'required',
                    'sumber_dana'       => 'required',
                    'disimpan_ke'       => 'required',
                    'nominal'           => 'required'
                ]);

                if ($validate->fails()) {
                    return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
                }

                $relasi = '';
                if ($request->relasi) $relasi = $request->relasi;
                $insert = [
                    'business_id'       => Session::get('business_id'),
                    'tgl_transaksi'     => (string) Tanggal::tglNasional($request->tgl_transaksi),
                    'rekening_debit'    => (string) $request->disimpan_ke,
                    'rekening_kredit'   => (string) $request->sumber_dana,
                    'usage_id'          => 0,
                    'installation_id'   => 0,
                    'relasi'            => (string) $relasi,
                    'total'             => str_replace(',', '', str_replace('.00', '', $request->nominal)),
                    'keterangan'        => (string) $request->keterangan,
                    'user_id'           => auth()->user()->id,
                ];

                if ($request->transaction_id) {
                    $insert['transaction_id'] = $request->transaction_id;
                }

                $transaksi = Transaction::create($insert);
                $msg = 'Transaksi ' . $insert['keterangan'] . ' berhasil disimpan';
            }
        }

        $trx = Transaction::where('id', $transaksi->id)->with([
            'rek_debit',
            'rek_kredit'
        ])->first();

        $view = view('transaksi.jurnal_umum.partials.notifikasi')->with(compact('trx', 'keuangan'))->render();
        return response()->json([
            'success'   => true,
            'msg'       => $msg,
            'view'      => $view,
        ]);
    }

    /**
     * Create data Pelunasan Instalasi.
     */
    private function Createpelunasaninstalasi($request)
    {
        $data = $request->only([
            "tgl_transaksi",
            "istallation_id",
            "abodemen",
            "biaya_sudah_dibayar",
            "tagihan",
            "pembayaran",
        ]);

        $data['tagihan'] = str_replace(',', '', $data['tagihan']);
        $data['tagihan'] = str_replace('.00', '', $data['tagihan']);
        $data['tagihan'] = floatval($data['tagihan']);

        $data['biaya_sudah_dibayar'] = str_replace(',', '', $data['biaya_sudah_dibayar']);
        $data['biaya_sudah_dibayar'] = str_replace('.00', '', $data['biaya_sudah_dibayar']);
        $data['biaya_sudah_dibayar'] = floatval($data['biaya_sudah_dibayar']);

        $data['pembayaran'] = str_replace(',', '', $data['pembayaran']);
        $data['pembayaran'] = str_replace('.00', '', $data['pembayaran']);
        $data['pembayaran'] = floatval($data['pembayaran']);

        $tagihan = $data['tagihan'];
        $biaya_sudah_dibayar = $data['biaya_sudah_dibayar'] ?? null;
        $biaya_instalasi = $data['pembayaran'];

        $penjumlahantrx = $biaya_sudah_dibayar + $biaya_instalasi;

        $biaya_instal = $data['tagihan'] - $penjumlahantrx;

        $jumlah_instal = ($biaya_instal >= 0) ? $biaya_instalasi : $biaya_sudah_dibayar;

        $persen = ($penjumlahantrx / $tagihan) * 100;

        $rekening_debit = Account::where([
            ['kode_akun', '1.1.01.01'],
            ['business_id', Session::get('business_id')]
        ])->first();
        $rekening_kredit = Account::where([
            ['kode_akun', '4.1.01.01'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $transaksi = Transaction::create([
            'business_id' => Session::get('business_id'),
            'rekening_debit' => $rekening_debit->id,
            'rekening_kredit' => $rekening_kredit->id,
            'tgl_transaksi' => Tanggal::tglNasional($request->tgl_transaksi),
            'total' => $jumlah_instal,
            'installation_id' => $request->istallation_id,
            'keterangan' => 'Biaya istalasi ' . $persen . '%',
        ]);

        if ($biaya_instal <= 0) {
            Installations::where('id', $request->istallation_id)->update([
                'status' => 'R',
            ]);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Pembayaran berhasil disimpan',
            'transaksi' => $transaksi,
            'transaction_id' => $transaksi->id
        ]);
    }

    public function struk_instalasi($id)
    {
        $keuangan = new Keuangan;

        $bisnis = Business::where('id', Session::get('business_id'))->first();
        $trx = Transaction::where('id', $id)->with([
            'Installations.customer'
        ])->first();
        $user = User::where('business_id', Session::get('business_id'))->where('id', $trx->user_id)->first();
        $kode_akun = Account::where('business_id', Session::get('business_id'))->where('id', $trx)->value('kode_akun');

        $jenis = 'Pembayaran Instalasi';
        $dari = ucwords($trx->Installations->customer->nama);
        $oleh = ucwords(auth()->user()->nama);

        $logo = $bisnis->logo;
        if (empty($logo)) {
            $gambar = '/storage/logo/1.png';
        } else {
            $gambar = '/storage/logo/' . $logo;
        }

        return view('transaksi.dokumen.struk_installasi')->with(compact('trx', 'keuangan', 'dari', 'oleh', 'jenis', 'bisnis', 'gambar'));
    }

    /**
     * .cetak detail Transaksi Instalasi
     */
    public function detailTransaksiInstalasi(Request $request)
    {
        $keuangan = new Keuangan;

        $data['installation_id'] = $request->id;
        $data['rek_debit'] = $request->rek_debit;
        $data['rek_kredit'] = $request->rek_kredit;

        $data['judul'] = 'Detail Transaksi';
        $data['sub_judul'] = 'Pasang Baru';
        $data['transaksi'] = Transaction::where(function ($query) use ($data) {
            $query->where('installation_id', $data['installation_id']);
        })->with([
            'Installations.customer',
            'Usages',
            'rek_debit',
            'rek_kredit'
        ])->orderBy('tgl_transaksi', 'ASC')->orderBy('urutan', 'ASC')->orderBy('id', 'ASC')->get();
        return [
            'label' => '<i class="fas fa-book"></i> Detail Transaksi ' . $data['sub_judul'],
            'view' => view('transaksi.partials.detail_instalasi', $data)->render(),
        ];
    }

    /**
     * Create data Pelunasan bulanan.
     */
    private function CreateTagihanBulanan($request)
    {
        $data = $request->only([
            "tgl_transaksi",
            "id_instal",
            "id_usage",
            "pembayaran",
            "tagihan",
            "keterangan",
            "denda",
            "abodemen"
        ]);

        $data['tagihan'] = str_replace(',', '', $data['tagihan']);
        $data['tagihan'] = str_replace('.00', '', $data['tagihan']);
        $data['tagihan'] = floatval($data['tagihan']);

        $data['pembayaran'] = str_replace(',', '', $data['pembayaran']);
        $data['pembayaran'] = str_replace('.00', '', $data['pembayaran']);
        $data['pembayaran'] = floatval($data['pembayaran']);

        $data['abodemen'] = str_replace(',', '', $data['abodemen']);
        $data['abodemen'] = str_replace('.00', '', $data['abodemen']);
        $data['abodemen'] = floatval($data['abodemen']);

        $data['denda'] = str_replace(',', '', $data['denda']);
        $data['denda'] = str_replace('.00', '', $data['denda']);
        $data['denda'] = floatval($data['denda']);

        $biaya_tagihan = $data['tagihan'] + $data['abodemen'] + $data['denda'];
        $biaya_instalasi = $data['pembayaran'];

        $tgl_transaksi = Tanggal::tglNasional($data['tgl_transaksi']);

        $trx = Transaction::where('business_id', Session::get('business_id'))->where('usage_id', $request->id_usage)->where('tgl_transaksi', '>=', $tgl_transaksi);
        if ($trx->count() < 3) {
            $trx->delete();
        }

        $usage = Usage::where('id', $data['id_usage'])->with([
            'installation',
            'customers',
            'usage' => function ($query) use ($tgl_transaksi) {
                $query->where('tgl_akhir', '<=', $tgl_transaksi)->where('status', 'UNPAID');
            },
            'usersCater'
        ])->first();

        $tgl_transaksi = Tanggal::tglNasional($request->tgl_transaksi);
        $accounts = Account::where('business_id', Session::get('business_id'))
            ->whereIn('kode_akun', ['1.1.01.01', '1.1.03.01', '2.1.02.02', '4.1.01.02', '4.1.01.03', '4.1.01.04', '5.1.02.04'])
            ->get()
            ->keyBy('kode_akun');

        $kode_kas = $accounts['1.1.01.01'] ?: null;
        $kode_piutang = $accounts['1.1.03.01'] ?: null;
        $kode_abodemen = $accounts['4.1.01.02'] ?: null;
        $kode_pemakaian = $accounts['4.1.01.03'] ?: null;
        $kode_denda = $accounts['4.1.01.04'] ?: null;

        $utang_komisi = $accounts['2.1.02.02'] ?: null;
        $beban_komisi = $accounts['5.1.02.04'] ?: null;

        $transaksi_piutang = Transaction::where('business_id', Session::get('business_id'))
            ->where('usage_id', $data['id_usage'])->where('installation_id', $data['id_instal'])
            ->where('rekening_debit', $kode_piutang->id)->where(function ($query) use ($kode_abodemen, $kode_pemakaian, $kode_denda) {
                $query->where('rekening_kredit', $kode_abodemen->id)
                    ->orWhere('rekening_kredit', $kode_pemakaian->id)
                    ->orWhere('rekening_kredit', $kode_denda->id);
            })->get();
        $saldo_piutang = $transaksi_piutang->sum('total'); // 120.000

        $riwayat_transaksi = Transaction::where('business_id', Session::get('business_id'))
            ->where('tgl_transaksi', '<=', $tgl_transaksi)
            ->where('usage_id', $data['id_usage'])->where('installation_id', $data['id_instal'])
            ->where('rekening_debit', $kode_kas->id)
            ->get();

        $rek_saldo_pemakaian = $riwayat_transaksi->where('rekening_kredit', $kode_pemakaian->id)->sum('total');
        $rek_saldo_abodemen = $riwayat_transaksi->where('rekening_kredit', $kode_abodemen->id)->sum('total');
        $rek_saldo_denda = $riwayat_transaksi->where('rekening_kredit', $kode_denda->id)->sum('total');
        $saldo_kas = $rek_saldo_pemakaian + $rek_saldo_abodemen + $rek_saldo_denda;

        $sumTransakksi = 0;
        $rek_saldo_pemakaian = 0;
        $rek_saldo_abodemen = 0;
        $rek_saldo_denda = 0;
        $insert = [];
        if ($saldo_kas < $saldo_piutang && $saldo_piutang > 0) {
            $rek_pemakaian = $kode_piutang->id;
            $rek_abodemen = $kode_piutang->id;
            $rek_denda = $kode_piutang->id;

            $data['tagihan'] = $data['tagihan'] - $rek_saldo_pemakaian;
            $data['abodemen'] = $data['abodemen'] - $rek_saldo_abodemen;
            $data['abodemen'] = $data['abodemen'] - $rek_saldo_denda;

            $sumTransakksi = $saldo_kas;
        }

        if ($saldo_piutang <= 0) {
            $rek_pemakaian = $kode_pemakaian->id;
            $rek_abodemen = $kode_abodemen->id;
            $rek_denda = $kode_denda->id;
        }

        if ($tgl_transaksi > $usage->tgl_akhir) {
            $insert[] = [
                'business_id' => Session::get('business_id'),
                'rekening_debit' => $kode_piutang->id,
                'rekening_kredit' => $kode_denda->id,
                'tgl_transaksi' => date('Y-m', strtotime('+1 month', strtotime($usage->tgl_akhir))) . '-01',
                'user_id' => auth()->user()->id,
                'usage_id' => $usage->id,
                'installation_id' => $usage->id_instalasi,
                'transaction_id' => 'PT-' . bin2hex(random_bytes(6)),
                'total' => $usage->installation->package->denda,
                'relasi' => $usage->customers->nama,
                'keterangan' => "Piutang Denda pemakaian atas nama " . $usage->customers->nama . ' (' . $usage->id_instalasi . ')',
                'urutan' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        $trx_id = 'TB-' . bin2hex(random_bytes(6));
        if ($data['abodemen'] != 0) {
            $insert[] = [
                'business_id' => Session::get('business_id'),
                'rekening_debit' => $kode_kas->id,
                'rekening_kredit' => $rek_abodemen,
                'tgl_transaksi' => $tgl_transaksi,
                'total' => $data['abodemen'],
                'installation_id' => $request->id_instal,
                'transaction_id' => $trx_id,
                'usage_id' => $request->id_usage,
                'user_id' => auth()->user()->id,
                'relasi' => $usage->customers->nama,
                'keterangan' => 'Pendapatan Abodemen pemakaian atas nama ' . $usage->customers->nama . ' (' . $usage->id_instalasi . ')',
                'urutan' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sumTransakksi += $data['abodemen'];
        }
        if ($data['tagihan'] != 0) {
            $insert[] = [
                'business_id' => Session::get('business_id'),
                'rekening_debit' => $kode_kas->id,
                'rekening_kredit' => $rek_pemakaian,
                'tgl_transaksi' => $tgl_transaksi,
                'total' => $data['tagihan'],
                'installation_id' => $request->id_instal,
                'transaction_id' => $trx_id,
                'usage_id' => $request->id_usage,
                'user_id' => auth()->user()->id,
                'relasi' => $usage->customers->nama,
                'keterangan' => 'Pendapatan Tagihan pemakaian atas nama ' . $usage->customers->nama . ' (' . $usage->id_instalasi . ')',
                'urutan' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sumTransakksi += $data['tagihan'];
        }
        if ($data['denda'] != 0) {
            $insert[] = [
                'business_id' => Session::get('business_id'),
                'rekening_debit' => $kode_kas->id,
                'rekening_kredit' => $rek_denda,
                'tgl_transaksi' => $tgl_transaksi,
                'total' => $data['denda'],
                'installation_id' => $request->id_instal,
                'transaction_id' => $trx_id,
                'usage_id' => $request->id_usage,
                'user_id' => auth()->user()->id,
                'relasi' => $usage->customers->nama,
                'keterangan' => 'Pendapatan Denda pemakaian atas nama ' . $usage->customers->nama . ' (' . $usage->id_instalasi . ')',
                'urutan' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sumTransakksi += $data['denda'];
        }

        $komisiId = str_replace('TB', 'BK', $trx_id);
        if (count($usage->usage) >= 3) {
            $insert[] = [
                'business_id' => Session::get('business_id'),
                'rekening_debit' => $beban_komisi->id,
                'rekening_kredit' => $utang_komisi->id,
                'tgl_transaksi' => $tgl_transaksi,
                'total' => $sumTransakksi * 0.1,
                'installation_id' => $request->id_instal,
                'transaction_id' => $komisiId,
                'usage_id' => $request->id_usage,
                'user_id' => auth()->user()->id,
                'relasi' => '-',
                'keterangan' => 'Utang Komisi 10% instalasi ' . $usage->installation->kode_instalasi,
                'urutan' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        Transaction::insert($insert);

        if ($biaya_instalasi  >= $biaya_tagihan) {
            Usage::where('business_id', Session::get('business_id'))->where('id', $request->id_usage)->update([
                'status' => 'PAID',
            ]);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Pembayaran berhasil disimpan',
            'transaksi' => $trx_id,
            'installation' => $usage->installation
        ]);
    }

    public function struk_tagihan($id)
    {
        $keuangan = new Keuangan;

        $bisnis = Business::where('id', Session::get('business_id'))->first();
        $trx_settings = Settings::where('business_id', Session::get('business_id'))->first();
        $trx = Transaction::where('transaction_id', $id)->with([
            'Installations.customer',
            'Usages',
            'transaction',
            'User'
        ])->first();
        $user = ($trx->User) ? $trx->User : null;
        $kode_akun = Account::where('business_id', Session::get('business_id'))->where('id', $trx)->value('kode_akun');

        $accounts = Account::where('business_id', Session::get('business_id'))
            ->whereIn('kode_akun', ['1.1.03.01', '4.1.01.02', '4.1.01.03', '4.1.01.04'])
            ->get()
            ->keyBy('kode_akun');

        $kode_piutang = $accounts['1.1.03.01'] ?: null;
        $kode_abodemen = $accounts['4.1.01.02'] ?: null;
        $kode_pemakaian = $accounts['4.1.01.03'] ?: null;
        $kode_denda = $accounts['4.1.01.04'] ?: null;

        $jenis = 'Pembayaran Bulanan';
        $dari = ucwords($trx->Installations->customer->nama);
        $oleh = ucwords(($user) ? $user->nama : '');

        $logo = $bisnis->logo;
        if (empty($logo)) {
            $gambar = '/storage/logo/1.png';
        } else {
            $gambar = '/storage/logo/' . $logo;
        }

        return view('transaksi.dokumen.struk_tagihan')->with(compact('trx', 'trx_settings', 'keuangan', 'dari', 'oleh', 'jenis', 'bisnis', 'gambar', 'kode_piutang', 'kode_abodemen', 'kode_pemakaian', 'kode_denda'));
    }

    /**
     * .cetak detail Transaksi Tagihan Bulanan
     */
    public function detailTransaksiTagihan(Request $request)
    {
        $installation_id = $request->id;
        $rek_debit = $request->rek_debit;
        $rek_kredit = $request->rek_kredit;

        $akun_kas = Account::where([
            ['business_id', Session::get('business_id')],
            ['kode_akun', '1.1.01.01']
        ])->first();

        // Ambil semua tahun unik dari transaksi
        $tahun_options = Transaction::where('installation_id', $installation_id)
            ->where('rekening_debit', $akun_kas->id)
            ->selectRaw('YEAR(tgl_transaksi) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Default tahun adalah tahun berjalan atau request
        $tahun = $request->tahun ?? date('Y');

        $transaksi = Transaction::where('installation_id', $installation_id)
            ->where('rekening_debit', $akun_kas->id)
            ->whereYear('tgl_transaksi', $tahun)
            ->with(['Installations.customer', 'Usages', 'rek_debit', 'rek_kredit'])
            ->orderBy('tgl_transaksi', 'DESC')
            ->get();

        $data = [
            'installation_id' => $installation_id,
            'rek_debit' => $rek_debit,
            'rek_kredit' => $rek_kredit,
            'akun_kas' => $akun_kas,
            'judul' => 'Detail Transaksi',
            'sub_judul' => 'Tagihan Bulanan',
            'tahun' => $tahun,
            'tahun_options' => $tahun_options,
            'transaksi' => $transaksi
        ];

        return [
            'label' => '<i class="fas fa-book"></i> Riwayat Pembayaran ',
            'view' => view('transaksi.partials.detail_tagihan', $data)->render(),
        ];
    }

    /**
     * Set saldo.
     */
    public function saldo($kode_akun)
    {
        $keuangan = new Keuangan;

        $total_saldo = 0;
        if (request()->get('tahun') || request()->get('bulan') || request()->get('hari')) {
            $data = [];
            $data['tahun'] = request()->get('tahun');
            $data['bulan'] = request()->get('bulan');
            $data['hari'] = request()->get('hari');
            $data['kode_akun'] = $kode_akun;

            $thn = $data['tahun'];
            $bln = $data['bulan'];
            $hari = $data['hari'];

            $tgl = $thn . '-' . $bln . '-';
            $bulan_lalu = date('m', strtotime('-1 month', strtotime($tgl . '01')));
            $awal_bulan = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu));
            if ($bln == 1) {
                $awal_bulan = $thn . '00-00';
            }

            $data['tgl_kondisi'] = $tgl;
            $kode_akun_by_id = $data['kode_akun'];
            $kode_akun = Account::where('business_id', Session::get('business_id'))->where('id', $kode_akun_by_id)->value('kode_akun');

            $data['rek'] = Account::where('business_id', Session::get('business_id'))->where('kode_akun', $kode_akun)->with([
                'amount' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                },
            ])->first();

            $total_saldo = $keuangan->komSaldo($data['rek']);
        }

        return response()->json([
            'saldo' => $total_saldo
        ]);
    }

    /**
     * .cetakdetailTransaksi jurnal umum
     */
    public function detailTransaksi(Request $request)
    {
        $keuangan = new Keuangan;

        $kode_akun_by_id = $request->account_id;
        $data['tahun'] = $request->tahun;
        $data['bulan'] = $request->bulan;
        $data['hari'] = $request->hari;

        if ($data['tahun'] == null) {
            abort(404);
        }

        $data['bulanan'] = true;
        if ($data['bulan'] == null) {
            $data['bulanan'] = false;
            $data['bulan'] = '12';
        }

        $data['harian'] = true;
        if ($data['hari'] == null) {
            $data['harian'] = false;
            $data['hari'] = date('t', strtotime($data['tahun'] . '-' . $data['bulan'] . '-01'));
        }

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Tahunan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        $awal_bulan = $thn . '00-00';
        if ($data['bulanan']) {
            $tgl = $thn . '-' . $bln . '-';
            $data['judul'] = 'Laporan Bulanan';
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $bulan_lalu = date('m', strtotime('-1 month', strtotime($tgl . '01')));
            $awal_bulan = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu));
            if ($bln == 1) {
                $awal_bulan = $thn . '00-00';
            }
        }

        if ($data['harian']) {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Harian';
            $data['sub_judul'] = 'Tanggal ' . $hari . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
            $awal_bulan = date('Y-m-d', strtotime('-1 day', strtotime($tgl)));
        }

        $data['tgl_kondisi'] = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];

        $data['is_dir'] = (auth()->guard('web')->user()->level == 1 && (auth()->guard('web')->user()->jabatan == 1 || auth()->guard('web')->user()->jabatan == 3)) ? true : false;
        $data['is_ben'] = (auth()->guard('web')->user()->level == 1 && (auth()->guard('web')->user()->jabatan == 3)) ? true : false;

        $data['rek'] = Account::where('business_id', Session::get('business_id'))->where('id', $kode_akun_by_id)->first();
        $data['transaksi'] = Transaction::where('tgl_transaksi', 'LIKE', '%' . $tgl . '%')->where(function ($query) use ($kode_akun_by_id) {
            $query->where('rekening_debit', $kode_akun_by_id)->orwhere('rekening_kredit', $kode_akun_by_id);
        })->with([
            'user',
            'rek_debit',
            'rek_kredit'
        ])->orderBy('tgl_transaksi', 'ASC')->orderBy('urutan', 'ASC')->orderBy('id', 'ASC')->get();

        $data['keuangan'] = $keuangan;
        $data['saldo'] = $keuangan->saldoAwal($data['tgl_kondisi'], $kode_akun_by_id);
        $data['d_bulan_lalu'] = $keuangan->saldoD($awal_bulan, $kode_akun_by_id);
        $data['k_bulan_lalu'] = $keuangan->saldoK($awal_bulan, $kode_akun_by_id);

        return [
            'label' => '<i class="fas fa-book"></i> ' . $data['rek']->kode_akun . ' - ' . $data['rek']->nama_akun . ' ' . $data['sub_judul'],
            'view' => view('transaksi.jurnal_umum.partials.jurnal', $data)->render(),
            'cetak' => view('transaksi.jurnal_umum.partials._jurnal', $data)->render()
        ];
    }

    /**
     * .cetak kuitansi notifikasi jurnal umum
     */


    public function cetak(Request $request)
    {
        $keuangan = new Keuangan;
        $id = $request->cetak;

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['transaksi'] = Transaction::whereIn('id', $id)
            ->selectRaw('*, SUM(total) as total_sum')
            ->groupBy('id')
            ->with('rek_debit', 'rek_kredit')
            ->get();
        $data['dir'] = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '1'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $data['sekr'] = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '2'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $logo = $data['bisnis']->logo;
        $data['gambar'] = $logo;
        $data['keuangan'] = $keuangan;

        $view = view('transaksi.jurnal_umum.dokumen.cetak', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    public function kuitansi($id)
    {
        $keuangan = new Keuangan;

        $bisnis = Business::where('id', Session::get('business_id'))->first();
        $trx = Transaction::where('id', $id)->first();
        $user = User::where('business_id', Session::get('business_id'))->where('id', $trx->user_id)->first();

        $kode_akun = Account::where('business_id', Session::get('business_id'))->where('id', $trx)->value('kode_akun');

        $jenis = 'BKM';
        $dari = ucwords($trx->relasi);
        $oleh = ucwords(auth()->user()->nama);
        $dibayar = ucwords($trx->relasi);
        if ($trx->rekening_kredit == '1.1.01.01' or ($keuangan->startWith($trx->rekening_kredit, '1.1.02') || $keuangan->startWith($trx->rekening_kredit, '1.1.01'))) {
            $jenis = 'BKK';
            $dari = ucwords($bisnis->nama);
            $oleh = ucwords($trx->relasi);
            $dibayar = ucwords($user->nama);
        }

        $logo = $bisnis->logo;
        if (empty($logo)) {
            $gambar = '/storage/logo/1.png';
        } else {
            $gambar = '/storage/logo/' . $logo;
        }

        return view('transaksi.jurnal_umum.dokumen.kuitansi')->with(compact('trx', 'bisnis', 'jenis', 'dari', 'oleh', 'dibayar', 'gambar', 'keuangan'));
    }

    public function kuitansi_thermal($id)
    {
        $kertas = '80';
        if (request()->get('kertas')) {
            $kertas = request()->get('kertas');
        }

        $keuangan = new Keuangan;

        $business = Business::where('id', Session::get('business_id'))->first();
        $trx = Transaction::where('id', $id)->first();
        $user = User::where('business_id', Session::get('business_id'))->where('id', $trx->user_id)->first();

        $jenis = 'BKM';
        $dari = ucwords($trx->relasi);
        $oleh = ucwords(auth()->user()->nama);
        $dibayar = ucwords($trx->relasi);
        if ($trx->rekening_kredit == '1.1.01.01' or ($keuangan->startWith($trx->rekening_kredit, '1.1.02') || $keuangan->startWith($trx->rekening_kredit, '1.1.01'))) {
            $jenis = 'BKK';
            $dari = ucwords($business->nama);
            $oleh = ucwords($trx->relasi);
            $dibayar = ucwords($user->nama);
        }

        $logo = $business->logo;
        if (empty($logo)) {
            $gambar = '/storage/logo/1.png';
        } else {
            $gambar = '/storage/logo/' . $logo;
        }

        return view('transaksi.jurnal_umum.dokumen.kuitansi_thermal')->with(compact('trx', 'business', 'jenis', 'dari', 'oleh', 'dibayar', 'gambar', 'keuangan', 'kertas'));
    }

    public function bkk($id)
    {
        $keuangan = new Keuangan;

        $business = Business::where('id', Session::get('business_id'))->first();
        $trx = Transaction::where('id', $id)->with('rek_debit')->with('rek_kredit')->first();

        $user = User::where('business_id', Session::get('business_id'))->where('id', $trx->user_id)->first();

        $dir = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '1'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $sekr = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '2'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $logo = $business->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.jurnal_umum.dokumen.bkk')->with(compact('trx', 'business', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function bkm($id)
    {
        $keuangan = new Keuangan;

        $business = Business::where('id', Session::get('business_id'))->first();
        $trx = Transaction::where('id', $id)->with('rek_debit')->with('rek_kredit')->first();
        $user = User::where('business_id', Session::get('business_id'))->where('id', $trx->id_user)->first();
        $dir = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '1'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $sekr = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '2'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $logo = $business->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.jurnal_umum.dokumen.bkm')->with(compact('trx', 'business', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function bm($id)
    {
        $keuangan = new Keuangan;

        $business = Business::where('id', Session::get('business_id'))->first();
        $trx = Transaction::where('id', $id)->with('rek_debit')->with('rek_kredit')->first();
        $user = User::where('business_id', Session::get('business_id'))->where('id', $trx->user_id)->first();

        $dir = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '1'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $sekr = User::where('business_id', Session::get('business_id'))->where([
            ['jabatan', '2'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $logo = $business->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.jurnal_umum.dokumen.bm')->with(compact('trx', 'business', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function data($id)
    {
        $trx = Transaction::where('id', $id)->first();
        return response()->json([
            'id' => $trx->id,
            'installation_id' => $trx->installation_id,
            'total' => number_format($trx->total)
        ]);
    }

    public function reversal(Request $request)
    {
        $id = $request->rev_id;
        $install = $request->rev_istal_id;

        $bulan = 0;
        $tahun = 0;
        $kode_akun = [];

        $trx = Transaction::where('id', $id)->first();

        $tgl = explode('-', $trx->tgl_transaksi);
        $tahun = $tgl[0];
        $bulan = $tgl[1];

        $kode_akun[$trx->rekening_debit] = $trx->rekening_debit;
        $kode_akun[$trx->rekening_kredit] = $trx->rekening_kredit;

        $reversal = Transaction::create([
            'business_id' => Session::get('business_id'),
            'tgl_transaksi' => (string) date('Y-m-d'),
            'rekening_debit' => (string) $trx->rekening_debit,
            'rekening_kredit' => (string) $trx->rekening_kredit,
            'usage_id' => $trx->usage_id,
            'installation_id' => $trx->installation_id,
            'keterangan' => (string) 'KOREKSI idt (' . $id . ') : ' . $trx->keterangan,
            'relasi' => (string) $trx->relasi,
            'total' => ($trx->total * -1),
            'urutan' => $trx->urutan,
            'id_user' => auth()->user()->id
        ]);


        return response()->json([
            'success' => true,
            'msg' => 'Transaksi Reversal untuk id ' . $id . ' dengan nominal berhasil.',
            'tgl_transaksi' => date('Y-m-d'),
            'id_Transaksi' => $install,
            'kode_akun' => implode(',', $kode_akun),
            'bulan' => str_pad($bulan, 2, "0", STR_PAD_LEFT),
            'tahun' => $tahun
        ]);
    }

    public function hapus(Request $request)
    {
        $id = $request->del_id;
        $installation_id = $request->del_instal_id;

        if ($id != '0') {
            $transaction = Transaction::find($id);

            if ($transaction) {
                $usageId = $transaction->usage_id;
                $usage = Usage::find($usageId);

                if ($usage) {
                    // Update status menjadi UNPAID
                    $usage->update([
                        'status' => 'UNPAID'
                    ]);
                }
            }
        }


        if ($installation_id != '0') {
            $instal = Installations::where('id', $installation_id)->update([
                'status' => 'I'
            ]);
        }

        $rek_inventaris = ['1.2.01.01', '1.2.01.02', '1.2.01.03', '1.2.01.04', '1.2.03.01', '1.2.03.02', '1.2.03.03', '1.2.03.04'];

        $trx = Transaction::where('id', $id)->first();
        if (in_array($trx->rekening_debit, $rek_inventaris)) {
            $jenis = intval(explode('.', $trx->rekening_debit)[2]);
            $kategori = intval(explode('.', $trx->rekening_debit)[3]);
            $nama_barang = trim(explode(')', $trx->keterangan_transaksi)[1]);

            $inv = Inventory::Where([
                ['jenis', $jenis],
                ['kategori', $kategori],
                ['tgl_beli', $trx->tgl_transaksi],
                ['nama_barang', $nama_barang]
            ])->delete();
        }

        $transaksi = Transaction::where('id', $id)->get();
        $trx = Transaction::where('id', $id)->delete();


        $bulan = 0;
        $tahun = 0;
        $kode_akun = [];
        foreach ($transaksi as $trx) {
            $tgl = explode('-', $trx->tgl_transaksi);
            $tahun = $tgl[0];
            $bulan = $tgl[1];

            $kode_akun[$trx->rekening_debit] = $trx->rekening_debit;
            $kode_akun[$trx->rekening_kredit] = $trx->rekening_kredit;
        }
        $kode_akun = array_values($kode_akun);

        return response()->json([
            'success' => true,
            'msg' => 'Transaksi Berhasil Dihapus.',
            'kode_akun' => implode(',', $kode_akun),
            'bulan' => str_pad($bulan, 2, "0", STR_PAD_LEFT),
            'tahun' => $tahun
        ]);
    }

    /**
     * ============================================================
     * REVERSAL PIUTANG TUNGGAKAN
     * ============================================================
     *
     * Kasus:
     * - Generate tunggakan berjalan tiap tanggal tertentu (lihat
     *   SystemController::dataset). Sistem otomatis insert transaksi
     *   piutang (D 1.1.03.01 / K 4.1.01.02-04) untuk semua usage
     *   ber-status UNPAID.
     * - Pelanggan kadang sudah bayar via transfer sebelum tanggal
     *   generate, namun karena pembayaran baru masuk / tercatat
     *   di aplikasi setelah generate tunggakan berjalan, maka
     *   usage.status tetap UNPAID dan transaksi piutang tunggakan
     *   sudah terlanjur dibuat. Piutang jadi tercatat padahal
     *   di bank uang sudah masuk.
     *
     * Solusi:
     * Menu "Reversal Piutang" menampilkan daftar instalasi yang
     * punya piutang tunggakan (usage.status = UNPAID + ada
     * transaksi piutang). Admin yang mengetahui pembayaran via
     * transfer dari mutasi bank dapat memilih bulan (usage)
     * yang akan di-reversal. Sistem akan menghapus transaksi
     * piutang tunggakan untuk usage_id tsb dan menghitung ulang
     * saldo akun bulan terkait. Status usage di-update ke PAID.
     *
     * CATATAN PENTING:
     * - Hanya menghapus transaksi piutang tunggakan (rekening_debit
     *   = piutang 1.1.03.01 dengan keterangan "Utang %").
     * - Hanya bulan yang dipilih (per usage_id) yang di-reversal.
     *   Piutang bulan-bulan sebelumnya yang memang belum bayar
     *   tetap aman.
     * - Setelah hapus, status usage di-update ke PAID (karena
     *   admin mengonfirmasi pembayaran via transfer sudah masuk).
     * - Saldo Amount dihitung ulang untuk bulan terkait supaya
     *   laporan keuangan akurat.
     * ============================================================
     */
    public function reversalPiutang(Request $request)
    {
        $businessId = Session::get('business_id');

        // Akun piutang (1.1.03.01) — di-load sekali.
        $kodePiutang = Account::where('business_id', $businessId)
            ->where('kode_akun', '1.1.03.01')
            ->first();

        // Default values untuk semua return path
        $installations = collect();
        $totalAll = 0;
        $perPage = 50;
        $page = 1;
        $totalPages = 0;
        $totalRows = 0;
        $filterTahun = null;
        $filterBulan = null;
        $search = '';
        $title = 'Reversal Piutang Tunggakan';

        if (! $kodePiutang) {
            return view('transaksi.reversal_piutang', compact(
                'title', 'installations', 'kodePiutang', 'totalAll', 'perPage',
                'page', 'totalPages', 'totalRows', 'filterTahun', 'filterBulan', 'search'
            ));
        }

        // Akun-akun pendapatan (4.1.01.02 | 03 | 04) — di-load sekali.
        $akunPendapatanIds = Account::where('business_id', $businessId)
            ->whereIn('kode_akun', ['4.1.01.02', '4.1.01.03', '4.1.01.04'])
            ->pluck('id')
            ->all();

        if (empty($akunPendapatanIds)) {
            return view('transaksi.reversal_piutang', compact(
                'title', 'installations', 'kodePiutang', 'totalAll', 'perPage',
                'page', 'totalPages', 'totalRows', 'filterTahun', 'filterBulan', 'search'
            ));
        }

        // Filter (optional): berdasarkan tahun piutang (tgl_akhir usage).
        $filterTahun = $request->get('tahun', null);
        $filterBulan = $request->get('bulan', null); // optional, 1-12
        $search = trim((string) $request->get('q', ''));

        // ============================================================
        // QUERY UTAMA (OPTIMIZED V2) — 1 query agregat, TANPA N+1
        // ============================================================
        // Ambil SEMUA agregat per (installation_id, usage_id) dalam
        // 1 query dengan GROUP BY. Filter by tahun, bulan, dan search.
        // Setelah itu filter by page di memory (lebih cepat drpd
        // subquery pagination di MySQL).
        $piutangDetailQuery = DB::table('transactions as t')
            ->join('usages as u', 'u.id', '=', 't.usage_id')
            ->leftJoin('installations as ins', 'ins.id', '=', 't.installation_id')
            ->leftJoin('customers as c', 'c.id', '=', 'ins.customer_id')
            ->where('t.business_id', $businessId)
            ->where('t.rekening_debit', $kodePiutang->id)
            ->whereIn('t.rekening_kredit', $akunPendapatanIds)
            ->where('t.keterangan', 'LIKE', 'Utang %')
            ->where('t.keterangan', 'NOT LIKE', 'Utang Komisi%')
            ->where('t.keterangan', 'NOT LIKE', 'Utang FEE%')
            ->where('t.keterangan', 'NOT LIKE', 'Utang Fee%')
            ->where('u.status', 'UNPAID');

        if ($filterTahun !== null && $filterTahun !== '') {
            $piutangDetailQuery->whereRaw('YEAR(u.tgl_akhir) = ?', [(int) $filterTahun]);
        }
        if ($filterBulan !== null && $filterBulan !== '') {
            $piutangDetailQuery->whereRaw('MONTH(u.tgl_akhir) = ?', [(int) $filterBulan]);
        }
        if ($search !== '') {
            $piutangDetailQuery->where(function ($q) use ($search) {
                $q->where('ins.kode_instalasi', 'LIKE', "%{$search}%")
                    ->orWhere('c.nama', 'LIKE', "%{$search}%");
            });
        }

        // Hitung total keseluruhan untuk header
        $totalAll = (clone $piutangDetailQuery)->sum('t.total');

        // Ambil agregat per (installation_id, usage_id) dalam 1 query
        // Sertakan tgl_akhir usage untuk sorting & display.
        $piutangPerUsage = (clone $piutangDetailQuery)
            ->groupBy('t.installation_id', 't.usage_id')
            ->select(
                't.installation_id',
                't.usage_id',
                DB::raw('SUM(t.total) as total_piutang'),
                'u.tgl_akhir'
            )
            ->get();

        if ($piutangPerUsage->isEmpty()) {
            return view('transaksi.reversal_piutang', compact(
                'title', 'installations', 'kodePiutang', 'totalAll',
                'perPage', 'page', 'totalPages', 'totalRows',
                'filterTahun', 'filterBulan', 'search'
            ));
        }

        // Agregasi di memory: per installation_id (untuk pagination)
        // dan per usage_id (untuk modal detail)
        $byInstallation = [];
        $allUsageIds = [];
        foreach ($piutangPerUsage as $row) {
            $insId = $row->installation_id;
            if (! isset($byInstallation[$insId])) {
                $byInstallation[$insId] = [
                    'installation_id' => $insId,
                    'total_piutang' => 0,
                    'tgl_akhir_max' => $row->tgl_akhir,
                    'piutang_per_usage' => [],
                ];
            }
            $byInstallation[$insId]['total_piutang'] += (float) $row->total_piutang;
            $byInstallation[$insId]['piutang_per_usage'][$row->usage_id] = (float) $row->total_piutang;
            if ($row->tgl_akhir > $byInstallation[$insId]['tgl_akhir_max']) {
                $byInstallation[$insId]['tgl_akhir_max'] = $row->tgl_akhir;
            }
            $allUsageIds[] = $row->usage_id;
        }

        // Sort by tgl_akhir_max desc, lalu paginate di memory
        $sorted = collect($byInstallation)->sortByDesc('tgl_akhir_max')->values();
        $totalRows = $sorted->count();
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = max(1, min($totalPages, (int) $request->get('page', 1)));

        $pageItems = $sorted->slice(($page - 1) * $perPage, $perPage);
        $installationIds = $pageItems->pluck('installation_id')->all();

        // Load instalasi + relasi (eager loading, TEPAT 50 rows max)
        $installations = Installations::whereIn('id', $installationIds)
            ->with(['customer', 'village', 'package'])
            ->get()
            ->keyBy('id');

        // Susun data akhir
        foreach ($pageItems as $item) {
            $insId = $item['installation_id'];
            if (! isset($installations[$insId])) continue;

            $ins = $installations[$insId];
            $ins->total_piutang = $item['total_piutang'];
            $ins->tgl_akhir_max = $item['tgl_akhir_max'];
            $ins->piutang_per_usage = $item['piutang_per_usage'];
            // Usage list di-load dari $allUsageIds yg tadi sudah diagregasi.
            // Kita tidak perlu query DB lagi untuk usage detail karena
            // tgl_akhir sudah cukup untuk display tabel utama.
            $ins->usage = collect();
        }

        $installations = $installations->values();

        return view('transaksi.reversal_piutang', compact(
            'title', 'installations', 'kodePiutang', 'totalAll',
            'perPage', 'page', 'totalPages', 'totalRows',
            'filterTahun', 'filterBulan', 'search'
        ));
    }

    /**
     * Detail transaksi piutang per usage (dipanggil via AJAX).
     * Menampilkan rincian akun (piutang/abodemen/pemakaian/denda)
     * yang akan dihapus.
     */
    public function detailReversalPiutang($installationId)
    {
        $businessId = Session::get('business_id');

        $kodePiutang = Account::where('business_id', $businessId)
            ->where('kode_akun', '1.1.03.01')
            ->first();

        if (! $kodePiutang) {
            return response()->json([
                'success' => false,
                'msg' => 'Akun piutang (1.1.03.01) tidak ditemukan.',
            ]);
        }

        $installation = Installations::where('id', $installationId)
            ->where('business_id', $businessId)
            ->with(['customer', 'village', 'package'])
            ->first();

        if (! $installation) {
            return response()->json([
                'success' => false,
                'msg' => 'Instalasi tidak ditemukan.',
            ]);
        }

        // Load akun pendapatan (4.1.01.02|03|04) untuk filter rekening_kredit
        $akunPendapatanIds = Account::where('business_id', $businessId)
            ->whereIn('kode_akun', ['4.1.01.02', '4.1.01.03', '4.1.01.04'])
            ->pluck('id')
            ->all();

        // QUERY UTAMA (dioptimasi): ambil semua transaksi piutang
        // untuk instalasi ini dalam 1 query (bukan N+1).
        $trxPiutangList = Transaction::where('installation_id', $installationId)
            ->where('business_id', $businessId)
            ->where('rekening_debit', $kodePiutang->id)
            ->whereIn('rekening_kredit', $akunPendapatanIds)
            ->where('keterangan', 'LIKE', 'Utang %')
            ->where('keterangan', 'NOT LIKE', 'Utang Komisi%')
            ->where('keterangan', 'NOT LIKE', 'Utang FEE%')
            ->where('keterangan', 'NOT LIKE', 'Utang Fee%')
            ->with(['rek_debit', 'rek_kredit'])
            ->get();

        // Kumpulkan usage_id unik yang punya piutang
        $usageIds = $trxPiutangList->pluck('usage_id')->unique()->all();

        // Load usage UNPAID dalam 1 query
        $usages = Usage::whereIn('id', $usageIds)
            ->where('status', 'UNPAID')
            ->orderBy('tgl_akhir', 'desc')
            ->get()
            ->map(function ($usage) use ($trxPiutangList) {
                // Filter transaksi piutang untuk usage ini dari list yg sudah di-load
                $usage->trx_piutang = $trxPiutangList->where('usage_id', $usage->id)->values();
                $usage->total_piutang = $usage->trx_piutang->sum('total');
                return $usage;
            });

        $view = view('transaksi.partials.detail_reversal_piutang', compact('installation', 'usages'))->render();

        return response()->json([
            'success' => true,
            'view' => $view,
            'installation_id' => $installation->id,
        ]);
    }

    /**
     * Proses reversal:
     * - Ambil array usage_id[] dari request
     * - Hapus transaksi piutang tunggakan (D 1.1.03.01) untuk tiap usage_id
     * - Hitung ulang saldo Amount untuk akun piutang & akun lawan
     *   di bulan tgl_akhir tiap usage
     */
    public function prosesReversalPiutang(Request $request)
    {
        $businessId = Session::get('business_id');
        $usageIds = $request->input('usage_ids', []);

        if (empty($usageIds) || ! is_array($usageIds)) {
            return response()->json([
                'success' => false,
                'msg' => 'Pilih minimal satu bulan pemakaian yang akan di-reversal.',
            ]);
        }

        $kodePiutang = Account::where('business_id', $businessId)
            ->where('kode_akun', '1.1.03.01')
            ->first();

        if (! $kodePiutang) {
            return response()->json([
                'success' => false,
                'msg' => 'Akun piutang (1.1.03.01) tidak ditemukan.',
            ]);
        }

        DB::beginTransaction();
        try {
            $totalDeleted = 0;
            $saldoAccounts = []; // akun2 yg perlu dihitung ulang saldonya

            foreach ($usageIds as $usageId) {
                $usage = Usage::find($usageId);
                if (! $usage) continue;

                // Kumpulkan transaksi piutang tunggakan untuk usage ini.
                // Filter PRESISI: hanya jurnal piutang yang D piutang
                // (1.1.03.01) dan K akun pendapatan (4.1.01.02|03|04).
                // Exclude 'Utang Komisi' / 'Utang FEE' untuk keamanan.
                $trxList = Transaction::where('usage_id', $usageId)
                    ->where('rekening_debit', $kodePiutang->id)
                    ->where('keterangan', 'LIKE', 'Utang %')
                    ->where('keterangan', 'NOT LIKE', 'Utang Komisi%')
                    ->where('keterangan', 'NOT LIKE', 'Utang FEE%')
                    ->where('keterangan', 'NOT LIKE', 'Utang Fee%')
                    ->whereIn('rekening_kredit', function ($q) use ($businessId) {
                        $q->select('id')->from('accounts')
                            ->where('business_id', $businessId)
                            ->where(function ($qq) {
                                $qq->where('kode_akun', '4.1.01.02')
                                    ->orWhere('kode_akun', '4.1.01.03')
                                    ->orWhere('kode_akun', '4.1.01.04');
                            });
                    })
                    ->get();

                foreach ($trxList as $trx) {
                    // Tandai akun2 yg terlibat untuk re-saldo
                    $saldoAccounts[$trx->tgl_transaksi][$trx->rekening_debit] = true;
                    $saldoAccounts[$trx->tgl_transaksi][$trx->rekening_kredit] = true;
                    $trx->delete();
                    $totalDeleted++;
                }
                // CATATAN: usage.status TIDAK diubah. Tetap UNPAID.
                // Alasan: admin hanya mengoreksi mekanisme cell-nya,
                // status bayar di aplikasi tetap mengikuti input
                // pembayaran terpisah (lewat menu Tagihan Bulanan / dll).
            }

            // Hitung ulang saldo Amount untuk akun-akun terkait per bulan
            // Pengaruh ke saldo bulan tgl_transaksi tsb (akun piutang & akun lawan).
            foreach ($saldoAccounts as $tglTransaksi => $akunMap) {
                foreach (array_keys($akunMap) as $accountId) {
                    $this->refreshSaldoAkun($accountId, $tglTransaksi, $businessId);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => "Berhasil me-reversal {$totalDeleted} transaksi piutang tunggakan.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'msg' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Hitung ulang saldo Amount untuk 1 akun pada bulan tertentu.
     * Mirip method saldo() di SystemController tapi untuk 1 akun
     * spesifik + handle multi-bulan dalam 1 tahun.
     */
    private function refreshSaldoAkun($accountId, $tglTransaksi, $businessId)
    {
        $tahun = (int) date('Y', strtotime($tglTransaksi));
        $bulan = (int) date('m', strtotime($tglTransaksi));
        $bulanLalu = str_pad($bulan - 1, 2, '0', STR_PAD_LEFT);
        $tglKondisi = date('Y-m-t', strtotime($tglTransaksi));

        $account = Account::where('business_id', $businessId)
            ->where('id', $accountId)
            ->with([
                'trx_debit' => fn($q) => $q->whereBetween('tgl_transaksi', ["{$tahun}-{$bulan}-01", $tglKondisi]),
                'trx_kredit' => fn($q) => $q->whereBetween('tgl_transaksi', ["{$tahun}-{$bulan}-01", $tglKondisi]),
                'oneAmount' => fn($q) => $q->where('tahun', $tahun)->where('bulan', $bulanLalu),
            ])->first();

        if (! $account) return;

        $id = $account->id . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $debit = ($account->oneAmount ? $account->oneAmount->debit : 0) + $account->trx_debit->sum('total');
        $kredit = ($account->oneAmount ? $account->oneAmount->kredit : 0) + $account->trx_kredit->sum('total');

        Amount::where('id', $id)->delete();
        Amount::insert([
            'id' => $id,
            'account_id' => $account->id,
            'tahun' => $tahun,
            'bulan' => str_pad($bulan, 2, '0', STR_PAD_LEFT),
            'debit' => $debit,
            'kredit' => $kredit,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Imports\CustomImport;
use App\Imports\FileImport;
use App\Models\Account;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Installations;
use App\Models\Menu;
use App\Models\Package;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\Usage;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AuthController extends Controller
{
    public function index()
    {
        $url = request()->getHost();
        if (str_contains($url, 'free.app') || str_contains($url, 'localhost') || str_contains($url, '192.168.')) {
            $url = 'siap_mapan.test';
        }

        $business = Business::where('domain', 'LIKE', '%' . $url . '%')->first();
        $domain = json_decode($business->domain, true);
        if ($domain[0] != $url && ! str_contains($url, 'siap_mapan.test')) {
            return redirect('https://' . $domain[0]);
        }

        return view('auth.login')->with(compact('business'));
    }

    public function register()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $url = request()->getHost();
        if (str_contains($url, 'free.app') || str_contains($url, 'localhost') || str_contains($url, '192.168.')) {
            $url = 'siap_mapan.test';
        }
        $data = $request->only(['username', 'password']);

        $validate = Validator::make($data, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->with('error', 'Username dan Password harus diisi');
        }

        // Eager-load business supaya 1 query saja (bukan 2).
        $user = User::with('business')->where('username', $data['username'])->first();
        if (! $user) {
            return redirect()->back()->with('error', 'Login Gagal. Username atau Password salah');
        }

        $business = $user->business;

        // Cache json_decode supaya tidak decode 3x seperti sebelumnya.
        $akses = json_decode($user->akses_menu, true) ?: [];

        $menu = Menu::where('parent_id', '0')->whereNotIn('id', $akses);
        if ($url != 'siap_mapan.test') {
            $menu = $menu->where('status', 'A');
        }

        $menu = $menu->with([
            'child' => function ($query) use ($akses, $url) {
                if ($url != 'siap_mapan.test') {
                    $query->whereNotIn('id', $akses)->where('status', 'A');
                } else {
                    $query->whereNotIn('id', $akses);
                }
            },
        ])->get();

        if (Auth::attempt($data)) {
            // Token disimpan HANYA di session (tidak nulis ke DB lagi).
            // Sebelumnya: User::where('id')->update(['auth_token' => ...])
            // setiap login, yang bikin cache user tidak berguna dan
            // middleware harus baca DB tiap request.
            $auth_token = md5(strtolower($data['username'] . '|' . $data['password']));

            $request->session()->regenerate();
            session([
                'nama_usaha' => $business->nama,
                'nama' => $user->nama,
                'jabatan' => $user->jabatan,
                'logo' => $business->logo,
                'business_id' => $business->id,
                'is_auth' => true,
                'auth_token' => $auth_token,
                'menu' => $menu,
            ]);

            if ($user->jabatan == '5') {
                return redirect('/dashboard/usagesDashboard/?cater_id=' . $user->id)->with('success', 'Selamat Datang ' . $user->nama);
            }

            return redirect('/')->with('success', 'Selamat Datang ' . $user->nama);
        }

        return redirect()->back()->with('error', 'Login Gagal. Username atau Password salah');
    }

    public function proses_register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'file' => 'file|mimes:xls,xlsx',
        ]);

        $data = $request->except('file', '_token');
        $file = $request->file('file');

        $business = Business::where('nama', $data['name'])->first();
        if (! $business) {
            $businessBaru = Business::create([
                'nama' => $data['name'],
                'telpon' => $data['telpon'],
                'email' => $data['email'],
                'alamat' => $data['alamat'],
            ]);

            $setting = Settings::orderBy('id')->first()->toArray();
            $setting['business_id'] = $businessBaru->id;

            unset($setting['id']);
            unset($setting['created_at']);
            unset($setting['updated_at']);

            $settingBusiness = Settings::create($setting);
        } else {
            $businessBaru = $business;
            $settingBusiness = Settings::where('business_id', $businessBaru->id)->first();
        }

        $file->storeAs('migrasi', $file->getClientOriginalName());
        $filename = 'migrasi/' . $file->getClientOriginalName();

        return $this->migrasi_file($businessBaru, $filename);
    }

    public function migrasi_file($business, $file)
    {
        $excel = (new FileImport)->toArray($file);

        $migrasiDesa = $excel['DESA'];
        $migrasiPaket = $excel['PAKET'];
        $migrasiInstalasi = $excel['INSTALASI'];

        Session::put('business_id_migrasi', $business->id);
        Session::put('migrasi_desa', $migrasiDesa);
        Session::put('migrasi_paket', $migrasiPaket);
        Session::put('migrasi_instalasi', $migrasiInstalasi);

        return view('auth.migrasi.proses');
    }

    public function migrasi_desa()
    {
        $migrasiDesa = Session::get('migrasi_desa');
        if ($migrasiDesa) {
            $header = 1;
            $data_kode = [];
            $data_desa = [];
            $business_id_migrasi = Session::get('business_id_migrasi');
            foreach ($migrasiDesa as $desa) {
                if ($header == 1 || ! $desa[1]) {
                    $header = 0;

                    continue;
                }

                $kode_desa = str_pad($desa[0], 4, '0', STR_PAD_LEFT);
                $business_id_migrasi = str_pad($business_id_migrasi, 3, '0', STR_PAD_LEFT);

                $kd_desa = $business_id_migrasi . '.' . $kode_desa;
                $data_desa[$desa[1]] = [
                    'kode' => $kd_desa,
                    'nama' => ucwords(strtolower($desa[1])),
                    'alamat' => '-',
                    'hp' => '08',
                ];

                $data_kode[] = $kd_desa;
            }

            Village::whereIn('kode', $data_kode)->delete();
            Village::insert($data_desa);

            return response()->json([
                'success' => true,
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'Migrasi data desa selesai',
                'next' => '/migrasi/paket',
                'open_tab' => false,
                'finish' => false,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    public function migrasi_paket()
    {
        $migrasiPaket = Session::get('migrasi_paket');
        if ($migrasiPaket) {
            $businessSetting = Settings::where('business_id', Session::get('business_id_migrasi'))->first();

            $header = 1;
            $data_paket = [];
            foreach ($migrasiPaket as $paket) {
                if ($header == 1) {
                    $header = 0;

                    continue;
                }

                $harga = [];
                for ($i = 2; $i < count($paket); $i++) {
                    if (is_numeric($paket[$i])) {
                        $harga[] = $paket[$i] ?: 0;
                    }
                }
                $harga = json_encode($harga);

                $data_paket[$paket[1]] = [
                    'business_id' => Session::get('business_id_migrasi'),
                    'kelas' => ucwords(strtolower($paket[1])),
                    'harga' => $harga,
                    'denda' => $businessSetting->denda,
                ];
            }

            Package::where('business_id', Session::get('business_id_migrasi'))->delete();
            Package::insert($data_paket);

            return response()->json([
                'success' => true,
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'Migrasi data paket selesai',
                'next' => '/migrasi/customer',
                'open_tab' => false,
                'finish' => false,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    public function migrasi_customer()
    {
        $migrasiCustomer = Session::get('migrasi_instalasi');
        if ($migrasiCustomer) {
            $data_desa = [];
            $business_id_migrasi = str_pad(Session::get('business_id_migrasi'), 3, '0', STR_PAD_LEFT);
            $desa = Village::where('kode', 'LIKE', $business_id_migrasi . '%')->get();
            foreach ($desa as $d) {
                $data_desa[$d->nama] = $d->id;
            }

            $header = 1;
            $data_cater = [];
            $data_customer = [];
            foreach ($migrasiCustomer as $customer) {
                // $customer = [
                //     0 => "KODE INSTALASI"
                //     1 => "PAKET"
                //     2 => "TANGGAL PASANG"
                //     3 => "DESA"
                //     4 => "PELANGGAN"
                //     5 => "CATER"
                //     6 => "ALAMAT INSTALASI"
                //     7 => "ABODEMEN"
                //     8 => "DENDA"
                //     9 => "BIAYA INSTALASI"
                //     10 => "TANGGAL TAGIHAN"
                //     11 => "AWAL"
                //     12 => "JANUARI"
                //     13 => "FEBRUARI"
                // ]

                if ($header == 1) {
                    $header = 0;

                    continue;
                }

                $id_desa = $data_desa[ucwords(strtolower($customer[3]))];
                $data_customer[$customer[0]] = [
                    'business_id' => Session::get('business_id_migrasi'),
                    'nama' => ucwords(strtolower($customer[4])),
                    'foto' => $customer[0],
                ];

                $cater = strtolower($customer[5]);
                if (! array_key_exists($cater, $data_cater)) {
                    $username = $cater . Session::get('business_id_migrasi');
                    $password = Hash::make($username);

                    $data_cater[$cater] = [
                        'business_id' => Session::get('business_id_migrasi'),
                        'nama' => ucwords(strtolower($cater)),
                        'jabatan' => '5',
                        'username' => $username,
                        'password' => $password,
                        'auth_token' => $cater,
                    ];
                }
            }

            Customer::where('business_id', Session::get('business_id_migrasi'))->delete();
            Customer::insert($data_customer);

            User::where([
                ['business_id', Session::get('business_id_migrasi')],
                ['jabatan', '5'],
            ])->delete();
            User::insert($data_cater);

            return response()->json([
                'success' => true,
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'Migrasi data customer selesai',
                'next' => '/migrasi/instalasi',
                'open_tab' => false,
                'finish' => false,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    public function migrasi_instalasi()
    {
        $migrasiInstalasi = Session::get('migrasi_instalasi');
        if ($migrasiInstalasi) {
            $data_desa = [];
            $business_id_migrasi = str_pad(Session::get('business_id_migrasi'), 3, '0', STR_PAD_LEFT);
            $desa = Village::where('kode', 'LIKE', $business_id_migrasi . '%')->get();
            foreach ($desa as $d) {
                $data_desa[$d->nama] = [
                    'id' => $d->id,
                    'kode' => $d->kode,
                ];
            }

            $customer = Customer::where('business_id', Session::get('business_id_migrasi'))->get();
            $data_customer = [];
            foreach ($customer as $cs) {
                $data_customer[$cs->foto] = $cs->id;
            }

            $data_cater = [];
            $cater = User::where([
                ['business_id', Session::get('business_id_migrasi')],
                ['jabatan', '5'],
            ])->get();
            foreach ($cater as $c) {
                $data_cater[$c->auth_token] = $c->id;
            }

            $data_paket = [];
            $paket = Package::where('business_id', Session::get('business_id_migrasi'))->get();
            foreach ($paket as $p) {
                $data_paket[$p->kelas] = $p->id;
            }

            $header = 1;
            $data_instalasi = [];
            foreach ($migrasiInstalasi as $instalasi) {
                // $instalasi = [
                //     0 => "KODE INSTALASI"
                //     1 => "PAKET"
                //     2 => "TANGGAL PASANG"
                //     3 => "DESA"
                //     4 => "PELANGGAN"
                //     5 => "CATER"
                //     6 => "ALAMAT INSTALASI"
                //     7 => "ABODEMEN"
                //     8 => "DENDA"
                //     9 => "BIAYA INSTALASI"
                //     10 => "TANGGAL TAGIHAN"
                //     11 => "AWAL"
                //     12 => "JANUARI"
                //     13 => "FEBRUARI"
                // ]

                if ($header == 1) {
                    $header = 0;

                    continue;
                }

                $id_desa = $data_desa[ucwords(strtolower($instalasi[3]))]['id'];
                // $kode_desa = $data_desa[ucwords(strtolower($customer[4]))]['kode'];
                // $urutan_desa = intval(explode('.', $kode_desa)[1]);

                $cater = strtolower($instalasi[5]);
                $cater_id = $data_cater[$cater];
                $customer_id = $data_customer[$instalasi[0]];

                $kode = explode('.', $instalasi[0]);
                $kode_instalasi = [];
                foreach ($kode as $key => $val) {
                    if (is_numeric($val)) {
                        $kode_instalasi[] = $val;
                    }
                }
                $kode_instalasi = implode('.', $kode_instalasi);
                // $kode_instalasi = $urutan_desa . '.' . str_pad($cater_id, 2, '0', STR_PAD_LEFT) . '.' . $kode[2];

                $paket = ucwords(strtolower($instalasi[1]));
                $paket_id = $data_paket[$paket];

                $data_instalasi[$instalasi[0]] = [
                    'kode_instalasi' => $kode_instalasi,
                    'customer_id' => $customer_id,
                    'business_id' => Session::get('business_id_migrasi'),
                    'cater_id' => $cater_id,
                    'package_id' => $paket_id,
                    'desa' => $id_desa,
                    'alamat' => $instalasi[6],
                    'abodemen' => $instalasi[7],
                    'biaya_instalasi' => $instalasi[9],
                    'order' => Date::excelToDateTimeObject($instalasi[2])->format('Y-m-d'),
                    'pasang' => Date::excelToDateTimeObject($instalasi[2])->format('Y-m-d'),
                    'aktif' => Date::excelToDateTimeObject($instalasi[2])->format('Y-m-d'),
                    'status' => 'A',
                ];
            }

            Installations::where('business_id', Session::get('business_id_migrasi'))->delete();
            Installations::insert($data_instalasi);

            return response()->json([
                'success' => true,
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'Migrasi data instalasi selesai',
                'next' => '/migrasi/pemakaian',
                'open_tab' => false,
                'finish' => false,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    public function migrasi_pemakaian()
    {
        $migrasiPemakaian = Session::get('migrasi_instalasi');
        if ($migrasiPemakaian) {
            $setting = Settings::where('business_id', Session::get('business_id_migrasi'))->first();
            $block = json_decode($setting->block, true);
            $data_block = [];
            foreach ($block as $key => $val) {
                $min = 0;
                $max = 0;
                $jarak = explode('-', $val['jarak']);
                foreach ($jarak as $k => $value) {
                    if ($k == 0) {
                        $min = trim(str_replace('M3', '', $value));
                    } else {
                        $max = trim(str_replace('M3', '', $value));
                    }
                }

                $data_block[$key] = [
                    'min' => $min,
                    'max' => $max,
                ];
            }

            $instalasi = Installations::where('business_id', Session::get('business_id_migrasi'))->get();
            $data_instalasi = [];
            foreach ($instalasi as $ins) {
                $data_instalasi[$ins->kode_instalasi] = [
                    'id' => $ins->id,
                    'customer_id' => $ins->customer_id,
                    'cater_id' => $ins->cater_id,
                    'package_id' => $ins->package_id,
                ];
            }

            $paket = Package::where('business_id', Session::get('business_id_migrasi'))->get();
            $data_paket = [];
            foreach ($paket as $pkt) {
                $data_paket[$pkt->id] = [
                    'harga' => $pkt->harga,
                    'denda' => $pkt->denda,
                ];
            }

            $header = 1;
            $data_pemakaian = [];
            $bulan = [];
            foreach ($migrasiPemakaian as $pemakaian) {
                // $instalasi = [
                //     0 => "KODE INSTALASI"
                //     1 => "PAKET"
                //     2 => "TANGGAL PASANG"
                //     3 => "DESA"
                //     4 => "PELANGGAN"
                //     5 => "CATER"
                //     6 => "ALAMAT INSTALASI"
                //     7 => "ABODEMEN"
                //     8 => "DENDA"
                //     9 => "BIAYA INSTALASI"
                //     10 => "TANGGAL TAGIHAN"
                //     11 => "AWAL"
                //     12 => "JANUARI"
                //     13 => "FEBRUARI"
                // ]

                if (count($bulan) == 0) {
                    for ($i = 11; $i < count($pemakaian); $i++) {
                        $bulan[$i] = $this->bulan($pemakaian[$i]);
                    }
                }

                if ($header == 1) {
                    $header = 0;

                    continue;
                }

                $kode = explode('.', $pemakaian[0]);
                $kode_instalasi = [];
                foreach ($kode as $key => $val) {
                    if (is_numeric($val)) {
                        $kode_instalasi[] = $val;
                    }
                }

                $kode_instalasi = implode('.', $kode_instalasi);
                $instalasi = $data_instalasi[$kode_instalasi];

                $id_instalasi = $instalasi['id'];
                $business_id_migrasi = Session::get('business_id_migrasi');
                $customer_id = $instalasi['customer_id'];
                $cater_id = $instalasi['cater_id'];
                $paket_id = $instalasi['package_id'];

                for ($i = 12; $i < count($pemakaian); $i++) {
                    $tanggal = Date::excelToDateTimeObject($pemakaian[10])->format('Y-m-d');
                    $thn = explode('-', $tanggal)[0];
                    $bln = $bulan[$i];
                    $hari = explode('-', $tanggal)[2];

                    $awal = $pemakaian[$i - 1];
                    $akhir = $pemakaian[$i];

                    $nominal = 0;
                    $jumlah = $akhir - $awal;
                    foreach ($data_block as $key => $value) {
                        if ($jumlah >= $value['min'] && $jumlah <= $value['max']) {
                            $paket_instalasi = $data_paket[$paket_id];
                            $harga = json_decode($paket_instalasi['harga'], true);

                            $nominal = $harga[$key] * $jumlah;
                        }
                    }

                    $tgl_pemakaian = $thn . '-' . $bln . '-' . $hari;
                    $data_pemakaian[] = [
                        'business_id' => $business_id_migrasi,
                        'id_instalasi' => $id_instalasi,
                        'customer' => $customer_id,
                        'awal' => $awal,
                        'akhir' => $akhir,
                        'jumlah' => $akhir - $awal,
                        'nominal' => $nominal,
                        'tgl_pemakaian' => $tgl_pemakaian,
                        'tgl_akhir' => date('Y-m', strtotime('+1 month', strtotime($tgl_pemakaian))) . '-27',
                        'cater' => $cater_id,
                        'status' => 'PAID',
                    ];
                }
            }

            Usage::where('business_id', Session::get('business_id_migrasi'))->delete();
            collect($data_pemakaian)->chunk(1000)->each(function ($chunk) {
                DB::table('usages')->insert($chunk->toArray());
            });

            return response()->json([
                'success' => true,
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'Migrasi data pemakaian selesai',
                'next' => '/migrasi/akun',
                'open_tab' => false,
                'finish' => false,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    public function migrasi_akun()
    {
        $rekening = Account::where('business_id', '1')->get();
        $data_rekening = [];
        foreach ($rekening as $rek) {
            $data_rekening[] = [
                'parent_id' => $rek->parent_id,
                'business_id_migrasi' => Session::get('business_id_migrasi'),
                'lev1' => $rek->lev1,
                'lev2' => $rek->lev2,
                'lev3' => $rek->lev3,
                'lev4' => $rek->lev4,
                'kode_akun' => $rek->kode_akun,
                'nama_akun' => $rek->nama_akun,
                'jenis_mutasi' => $rek->jenis_mutasi,
            ];
        }

        Account::where('business_id', Session::get('business_id_migrasi'))->delete();
        Account::insert($data_rekening);

        return response()->json([
            'success' => true,
            'time' => date('Y-m-d H:i:s'),
            'msg' => 'Migrasi akun selesai',
            'next' => '/migrasi/transaksi',
            'open_tab' => false,
            'finish' => false,
        ]);
    }

    public function migrasi_transaksi()
    {
        $businessId = Session::get('business_id_migrasi');

        $accounts = Account::where('business_id', $businessId)
            ->whereIn('kode_akun', ['1.1.01.01', '4.1.01.02', '4.1.01.03'])
            ->get()
            ->keyBy('kode_akun');

        $kode_kas = $accounts['1.1.01.01'] ?? null;
        $kode_abodemen = $accounts['4.1.01.02'] ?? null;
        $kode_tagihan = $accounts['4.1.01.03'] ?? null;

        $data_transaksi = [];
        $created_at = date('Y-m-d H:i:s');
        $usages = Usage::where('business_id', $businessId)->with('customers', 'installation')->get();
        foreach ($usages as $usage) {
            $data_transaksi[] = [
                'business_id' => $businessId,
                'tgl_transaksi' => $usage->tgl_akhir,
                'rekening_debit' => $kode_kas->id,
                'rekening_kredit' => $kode_abodemen->id,
                'user_id' => '1',
                'usage_id' => $usage->id,
                'installation_id' => $usage->id_instalasi,
                'total' => $usage->installation->abodemen,
                'denda' => '0',
                'relasi' => $usage->customers->nama,
                'keterangan' => 'Pembayaran Abodemen Pemakaian Atas Nama ' . $usage->customers->nama . ' (' . $usage->installation->id . ')',
                'urutan' => '0',
                'created_at' => $created_at,
            ];

            $data_transaksi[] = [
                'business_id' => $businessId,
                'tgl_transaksi' => $usage->tgl_akhir,
                'rekening_debit' => $kode_kas->id,
                'rekening_kredit' => $kode_tagihan->id,
                'user_id' => '1',
                'usage_id' => $usage->id,
                'installation_id' => $usage->id_instalasi,
                'total' => $usage->nominal,
                'denda' => '0',
                'relasi' => $usage->customers->nama,
                'keterangan' => 'Pembayaran Tagihan Bulanan Atas Nama ' . $usage->customers->nama . ' (' . $usage->installation->id . ')',
                'urutan' => '0',
                'created_at' => $created_at,
            ];
        }

        DB::statement('SET @DISABLE_TRIGGER = 1');
        Transaction::where('business_id', $businessId)->delete();
        collect($data_transaksi)->chunk(1000)->each(function ($chunk) {
            DB::table('transactions')->insert($chunk->toArray());
        });
        DB::statement('SET @DISABLE_TRIGGER = 0');

        return response()->json([
            'success' => true,
            'time' => date('Y-m-d H:i:s'),
            'msg' => 'Migrasi transaksi instalasi selesai',
            'next' => '/migrasi/sync',
            'open_tab' => false,
            'finish' => false,
        ]);
    }

    public function migrasi_sync()
    {
        User::insert([
            'nama' => 'Direktur',
            'jabatan' => '1',
            'username' => 'direktur' . Session::get('business_id_migrasi'),
            'password' => Hash::make('direktur' . Session::get('business_id_migrasi')),
            'business_id' => Session::get('business_id_migrasi'),
        ]);

        Session::flush();

        return response()->json([
            'success' => true,
            'time' => date('Y-m-d H:i:s'),
            'msg' => 'Migrasi selesai',
            'finish' => true,
        ]);
    }

    public function custom(Business $business)
    {
        $today = date('Y-m-d H:i:s');

        $denda = $business->setting->denda;
        $tgl_toleransi = $business->setting->tanggal_toleransi;

        $excel = (new CustomImport)->toArray('migrasi/DataTunggakanMulo.xlsx');

        $pemakaian = [];
        $bulanPemakaian = [];
        $dataTunggakan = $excel[0];
        foreach ($dataTunggakan as $index => $row) {
            if ($index == 0) {
                foreach ($row as $key => $value) {
                    if ($key >= 5) {
                        $bulanMenunggak = explode(' ', $value);

                        $bulan = str_pad($this->bulan($bulanMenunggak[0]), 2, '0', STR_PAD_LEFT);
                        $bulanPemakaian[$key] = $bulanMenunggak[1] . '-' . $bulan . '-01';
                    }
                }
            } else {
                $kode = explode('.', $row[4]);
                $kode_instalasi = [];
                foreach ($kode as $key => $val) {
                    if (is_numeric($val)) {
                        $kode_instalasi[] = trim($val);
                    }
                }

                $data = [];
                foreach ($row as $key => $value) {
                    if ($key >= 5) {
                        $data[$bulanPemakaian[$key]] = $value ?: 0;
                    }
                }

                $pemakaian['kode_instalasi'][] = implode('.', $kode_instalasi);
                $pemakaian['data'][] = [
                    'kode_instalasi' => implode('.', $kode_instalasi),
                    'bulan' => $data,
                ];
            }
        }

        $paket = Package::where('business_id', $business->id)->get()->pluck('harga', 'id')->toArray();
        $instalasi = Installations::where('business_id', $business->id)->whereIn('kode_instalasi', $pemakaian['kode_instalasi'])->get();

        $id_instalasi = $instalasi->pluck('id', 'kode_instalasi')->toArray();
        $paket_instalasi = $instalasi->pluck('package_id', 'kode_instalasi')->toArray();
        $abodemen_instalasi = $instalasi->pluck('abodemen', 'kode_instalasi')->toArray();
        $customer = $instalasi->pluck('customer_id', 'kode_instalasi')->toArray();

        $usages = [];
        foreach ($pemakaian['data'] as $data) {
            $kode_instalasi = $data['kode_instalasi'];

            if (isset($id_instalasi[$kode_instalasi])) {
                $id = $id_instalasi[$kode_instalasi];

                $paket_id = $paket_instalasi[$kode_instalasi];
                $customer_id = $customer[$kode_instalasi];
                $abodemen = $abodemen_instalasi[$kode_instalasi];
                $harga = json_decode($paket[$paket_id], true);

                foreach ($data['bulan'] as $tanggal => $jumlah) {
                    if ($jumlah > 0) {
                        $tgl_pemakaian = date('Y-m-d', strtotime('-1 month', strtotime($tanggal)));
                        $tgl_akhir = date('Y-m', strtotime($tanggal)) . '-' . $tgl_toleransi;

                        $harga_paket = $harga[0];
                        $jumlah_menunggak = $jumlah - $denda - $abodemen;
                        if ($jumlah_menunggak < 0) {
                            $jumlah_menunggak = 0;
                        }

                        if ($jumlah_menunggak / $harga_paket > 10) {
                            $harga_paket = $harga[1];
                        }

                        $jumlah_pakai = round($jumlah_menunggak / $harga_paket, 2);
                        $usage = [
                            'business_id' => $business->id,
                            'id_instalasi' => $id,
                            'customer' => $customer_id,
                            'awal' => 0,
                            'akhir' => 0,
                            'jumlah' => $jumlah_pakai,
                            'nominal' => $jumlah_menunggak,
                            'tgl_pemakaian' => $tgl_pemakaian,
                            'tgl_akhir' => $tgl_akhir,
                            'cater' => 1,
                            'status' => 'UNPAID',
                            'created_at' => $today,
                            'updated_at' => $today,
                        ];

                        $values = array_values($usage);
                        $values = array_map(function ($value) {
                            return is_string($value) ? "'" . $value . "'" : $value;
                        }, $values);

                        echo 'INSERT INTO usages (business_id, id_instalasi, customer, awal, akhir, jumlah, nominal, tgl_pemakaian, tgl_akhir, cater, status, created_at, updated_at) VALUES (' . implode(',', $values) . '); <br>';
                    }
                }
            }
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/auth')->with('success', 'Logout Berhasil');
    }

    private function bulan($nama_bulan)
    {
        $nama_bulan = strtoupper($nama_bulan);
        switch ($nama_bulan) {
            case 'JANUARI':
                return 1;
                break;

            case 'FEBRUARI':
                return 2;
                break;

            case 'MARET':
                return 3;
                break;

            case 'APRIL':
                return 4;
                break;

            case 'MEI':
                return 5;
                break;

            case 'JUNI':
                return 6;
                break;

            case 'JULI':
                return 7;
                break;

            case 'AGUSTUR':
                return 8;
                break;

            case 'SEPTEMBER':
                return 9;
                break;

            case 'OKTOBER':
                return 10;
                break;

            case 'NOVEMBER':
                return 11;
                break;

            case 'DESEMBER':
                return 12;
                break;

            default:
                1;
                break;
        }
    }
}

<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CaterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\InstallationsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PelaporanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VillageController;
use App\Models\Account;
use App\Models\Installations;
use App\Models\Transaction;
use App\Models\Usage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Example route web
// GET /customers                         index
// POST /customers                        store
// GET /customers/create                  create
// GET /customers/{customer}              show
// GET /customers/{customer}/edit         edit
// PUT /customers/{customer}              update
// DELETE /customers/{customer}           destroy


Route::prefix('master')->group(function () {
    Route::get('/', [HakAksesController::class, 'index']);
    Route::get('/hakakses/{id_user}', [HakAksesController::class, 'hakAkses']);
    Route::post('/hakakses/{id_user}', [HakAksesController::class, 'simpan']);
});

Route::middleware(['guest'])->group(function () {
    // Auth
    Route::get('/auth', [AuthController::class, 'index'])->name('auth');
    Route::post('/auth', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'register']);
    Route::post('/register', [AuthController::class, 'proses_register']);

    Route::get('/migrasi/desa', [AuthController::class, 'migrasi_desa']);
    Route::get('/migrasi/paket', [AuthController::class, 'migrasi_paket']);
    Route::get('/migrasi/customer', [AuthController::class, 'migrasi_customer']);
    Route::get('/migrasi/instalasi', [AuthController::class, 'migrasi_instalasi']);
    Route::get('/migrasi/pemakaian', [AuthController::class, 'migrasi_pemakaian']);
    Route::get('/migrasi/akun', [AuthController::class, 'migrasi_akun']);
    Route::get('/migrasi/transaksi', [AuthController::class, 'migrasi_transaksi']);
    Route::get('/migrasi/sync', [AuthController::class, 'migrasi_sync']);

    Route::get('/migrasi/custom/{business}', [AuthController::class, 'custom']);
});

Route::get('/link', function () {
    $target = __DIR__ . '/../storage/app/public';
    $shortcut = __DIR__ . '/../public/storage';

    try {
        symlink($target, $shortcut);

        return response()->json('Symlink created successfully.');
    } catch (\Exception $e) {
        return response()->json('Failed to create symlink: ' . $e->getMessage());
    }
});

Route::get('/delete-denda', function () {
    $rekening_denda = Account::where([
        ['kode_akun', '4.1.01.04'],
        ['business_id', Session::get('business_id')]
    ])->first();

    $rekening_piutang = Account::where([
        ['kode_akun', '1.1.03.01'],
        ['business_id', Session::get('business_id')]
    ])->first();

    $installations = Installations::where('business_id', Session::get('business_id'))->with([
        'usage.transaction'  => function ($query) use ($rekening_denda, $rekening_piutang) {
            $query->where([
                ['rekening_kredit', $rekening_denda->id],
                ['rekening_debit', $rekening_piutang->id]
            ]);
        }
    ])->get();

    $deleteTrx = [];
    foreach ($installations as $ins) {
        $piutangDenda = null;
        foreach ($ins->usage as $usage) {

            if ($piutangDenda != null) {
                $tgl_akhir_lalu = date('Y-m', strtotime('-1 month', strtotime($usage->tgl_akhir))) . '-01';
                $tanggal_piutang = date('Y-m', strtotime($piutangDenda)) . '-01';

                $tglAkhirLalu  = new DateTime($tgl_akhir_lalu);
                $tglPiutang = new DateTime($tanggal_piutang);
                $selisih  = $tglPiutang->diff($tglAkhirLalu);

                if ($selisih->m > 0 && $tgl_akhir_lalu > $tanggal_piutang) {
                    $deleteTrx[] = $usage->id;
                }
            }

            if (count($usage->transaction) > 0) {
                $trxDenda = $usage->transaction[0];
                $piutangDenda = $trxDenda->tgl_transaksi;
            }
        }
    }

    dd(implode(',', $deleteTrx));
});

Route::get('/import-excel', [ExcelController::class, 'index']);

Route::middleware(['auth', 'auth.token'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard/installations', [DashboardController::class, 'installations']);
    Route::get('/dashboard/usages', [DashboardController::class, 'usages']);
    Route::get('/dashboard/tagihan', [DashboardController::class, 'tagihan']);
    Route::get('/dashboard/tunggakan', [DashboardController::class, 'tunggakan']);
    Route::get('/dashboard/usagesDashboard', [DashboardController::class, 'usagesDasboard']);
    Route::get('/dashboard/sps/{id}', [DashboardController::class, 'sps']);
    Route::get('/dashboard/Cetaktunggakan2/{id}', [DashboardController::class, 'Cetaktunggakan2']);
    Route::get('/dashboard/Cetaktunggakan1/{id}', [DashboardController::class, 'Cetaktunggakan1']);
    Route::get('/dashboard/data-installations', [DashboardController::class, 'dataInstallations']);

    Route::get('/dataset/{time}', [SystemController::class, 'dataset']);

    Route::get('/pengaturan/coa', [SopController::class, 'coa']);
    Route::get('/profil', [ProfilController::class, 'index']);
    Route::post('/profil', [ProfilController::class, 'update']);
    Route::post('/profil/data_login', [ProfilController::class, 'data_login']);
    Route::resource('/accounts', AccountController::class);

    // Business || Usaha
    Route::resource('/business', BusinessController::class);

    // Customers || Pelanggan
    Route::resource('/customers', CustomerController::class);

    // Installations || Instalasi
    Route::get('/installations/daftar_instalasi', [InstallationsController::class, 'DaftarInstalasi']);
    Route::get('/installations/cater/{cater_id?}', [InstallationsController::class, 'list']);
    Route::get('/installations/reg_notifikasi/{customer_id}', [InstallationsController::class, 'reg_notifikasi']);
    Route::get('/installations/jenis_paket/{id}', [InstallationsController::class, 'jenis_paket']);
    Route::get('/installations/kode_instalasi', [InstallationsController::class, 'kode_instalasi']);
    Route::get('/installations/cetak/{installation}', [InstallationsController::class, 'cetak_pemakaian']);

    Route::get('/installations/CariPelunasan_Instalasi', [InstallationsController::class, 'CariPelunasanInstalasi']);
    Route::get('/installations/CariTagihan_bulanan', [InstallationsController::class, 'CariTagihanbulanan']);
    Route::get('/installations/usage/{kode_instalasi}', [InstallationsController::class, 'usage']);
    Route::get('/installations/KembaliStatus_A/{id}', [InstallationsController::class, 'KembaliStatus_A']);
    Route::post('/installations/BlokirStatus_B/{id}', [InstallationsController::class, 'BlokirStatus_B']);
    Route::post('/installations/HapusPelanggan/{id}', [InstallationsController::class, 'HapusPelanggan']);
    Route::resource('/installations', InstallationsController::class);

    // Packages || Paket
    Route::get('/packages/block_paket', [PackageController::class, 'block_paket']);
    Route::resource('/packages', PackageController::class);

    // Usages || Penggunaan
    Route::get('/usages/cari_anggota', [UsageController::class, 'carianggota']);
    Route::get('/usages/detail_tagihan/', [UsageController::class, 'detailTagihan']);
    Route::get('/usages/barcode/', [UsageController::class, 'barcode']);
    Route::post('/usages/cetak', [UsageController::class, 'cetak']);
    Route::post('/usages/sampah/cetak', [UsageController::class, 'cetakSampah']);
    Route::post('/usages/cetak_tagihan', [UsageController::class, 'cetak_tagihan']);
    Route::post('/usages/sampah/cetak_tagihan', [UsageController::class, 'tagihan_sampah']);
    Route::post('/usages/cetak_input', [UsageController::class, 'cetak_input']);


    Route::get('/usages/cater', [UsageController::class, 'cater']);
    Route::get('/usages/sampah', [UsageController::class, 'sampah']);
    Route::get('/usages/create/sampah', [UsageController::class, 'createSampah']);
    Route::post('/usages/storeSampah', [UsageController::class, 'storeSampah']);

    Route::resource('/usages', UsageController::class);

    // Transactions || Transaksi
    Route::get('/transactions/ambil_rekening/{id}', [TransactionController::class, 'rekening']);
    Route::get('/transactions/form_nominal/', [TransactionController::class, 'form']);
    Route::get('/transactions/jurnal_umum', [TransactionController::class, 'jurnal_umum']);
    Route::get('/transactions/tagihan_bulanan', [TransactionController::class, 'tagihan_bulanan']);
    Route::get('/transactions/pelunasan_instalasi', [TransactionController::class, 'pelunasan_instalasi']);
    Route::get('/transactions/saldo/{kode_akun}', [TransactionController::class, 'saldo']);
    Route::get('/transactions/detail_transaksi_tagihan/', [TransactionController::class, 'detailTransaksiTagihan']);
    Route::get('/transactions/detail_transaksi_instalasi/', [TransactionController::class, 'detailTransaksiInstalasi']);
    Route::get('/transactions/detail_transaksi/', [TransactionController::class, 'detailTransaksi']);

    Route::post('/transactions/tutup_buku/saldo_tutup_buku', [TransactionController::class, 'saldo_tutup_buku']);
    Route::get('/transactions/tutup_buku', [TransactionController::class, 'jurnalTutupBuku']);
    Route::post('/transactions/tutup_buku', [TransactionController::class, 'tutup_buku']);
    Route::post('/transactions/simpan_laba', [TransactionController::class, 'simpanAlokasiLaba']);

    Route::get('/transactions/ebudgeting', [TransactionController::class, 'ebudgeting']);
    Route::post('/transactions/anggaran', [TransactionController::class, 'anggaran']);
    Route::post('/transactions/simpan_anggaran', [TransactionController::class, 'simpananggaran']);

    Route::get('/transaction/komisi_sps', [TransactionController::class, 'komisi_sps']);

    Route::get('/transactions/dokumen/struk_instalasi/{id}', [TransactionController::class, 'struk_instalasi']);
    Route::get('/transactions/dokumen/struk_tagihan/{id}', [TransactionController::class, 'struk_tagihan']);
    Route::get('/transactions/dokumen/kuitansi/{id}', [TransactionController::class, 'kuitansi']);
    Route::get('/transactions/dokumen/kuitansi_thermal/{id}', [TransactionController::class, 'kuitansi_thermal']);
    Route::get('/transactions/dokumen/bkk/{id}', [TransactionController::class, 'bkk']);
    Route::get('/transactions/dokumen/bkm/{id}', [TransactionController::class, 'bkm']);
    Route::get('/transactions/dokumen/bm/{id}', [TransactionController::class, 'bm']);

    Route::post('/transactions/dokumen/cetak', [TransactionController::class, 'cetak']);
    Route::get('/transactions/data/{id}', [TransactionController::class, 'data']);
    Route::post('/transactions/reversal', [TransactionController::class, 'reversal']);
    Route::post('/transactions/hapus', [TransactionController::class, 'hapus']);
    Route::resource('/transactions', TransactionController::class);

    // Setting || Pengaturan
    Route::get('/pengaturan/sop', [SopController::class, 'profil']);
    Route::get('/pengaturan/coa', [SopController::class, 'coa']);
    Route::get('/pengaturan/akun_coa', [SopController::class, 'akun_coa']);
    Route::post('/pengaturan/coa', [SopController::class, 'CreateCoa']);
    Route::put('/pengaturan/coa/{kode_akun}', [SopController::class, 'UpdateCoa']);
    Route::delete('/pengaturan/coa/{account}', [SopController::class, 'DeleteCoa']);


    Route::get('/pengaturan/sop/pasang_baru', [SopController::class, 'pasang_baru']);
    Route::get('/pengaturan/sop/lembaga', [SopController::class, 'lembaga']);
    Route::get('/pengaturan/sop/sistem_instal', [SopController::class, 'sistem_instal']);
    Route::get('/pengaturan/sop/block_paket', [SopController::class, 'block_paket']);
    Route::post('/pengaturan/pesan_whatsapp', [SopController::class, 'pesan']);
    Route::put('/pengaturan/sop/logo/{business}', [SopController::class, 'logo']);
    Route::resource('/pengaturan', SopController::class);

    // Users || Pengguna
    Route::resource('/users', UserController::class);

    // Users || Desa
    Route::get('/ambil_kab/{kode}', [VillageController::class, 'ambil_kab']);
    Route::get('/ambil_kec/{kode}', [VillageController::class, 'ambil_kec']);
    Route::get('/ambil_desa/{kode}', [VillageController::class, 'ambil_desa']);
    Route::get('/set_alamat/{kode}', [VillageController::class, 'generateAlamat']);
    Route::resource('/villages', VillageController::class);
    Route::delete('/villages/{village}', [VillageController::class, 'destroy']);

    // Dusun
    // Route::resource('/hamlets', HamletController::class);
    // Route::delete('/hamlets/{hamlet}', [HamletController::class, 'destroy']);

    // Cater
    Route::resource('/caters', CaterController::class);

    //generate
    Route::get('/generate_alamat/{kode}', [VillageController::class, 'generateAlamat']);

    //pelaporan
    Route::get('/pelaporan', [PelaporanController::class, 'index']);
    Route::get('/pelaporan/preview', [PelaporanController::class, 'preview']);
    Route::get('/pelaporan/sub_laporan/{file}', [PelaporanController::class, 'subLaporan']);
    Route::get('/pelaporan/simpan_saldo/{tahun}/{bulan?}', [PelaporanController::class, 'simpanSaldo']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('/link', function () {
    $target = __DIR__ . '/../storage/app/public';
    $shortcut = __DIR__ . '/../public/storage';

    if (is_link($shortcut)) {
        return response()->json("Symlink already exists.");
    }

    if (file_exists($shortcut)) {
        if (is_dir($shortcut)) {
            rmdir($shortcut);
        } else {
            unlink($shortcut);
        }
    }

    if (PHP_OS_FAMILY === 'Windows') {
        try {
            $targetWin = str_replace('/', '\\', $target);
            $shortcutWin = str_replace('/', '\\', $shortcut);
            exec('mklink /D "' . $shortcutWin . '" "' . $targetWin . '"', $out, $rc);
            if ($rc === 0) {
                return response()->json("Symlink (mklink) created successfully.");
            }
            Artisan::call('storage:link');
            return response()->json("Fallback to artisan storage:link: " . Artisan::output());
        } catch (\Exception $e) {
            return response()->json("Failed: " . $e->getMessage());
        }
    }

    try {
        symlink($target, $shortcut);
        return response()->json("Symlink created successfully.");
    } catch (\Exception $e) {
        try {
            Artisan::call('storage:link');
            return response()->json("Failed, fallback artisan: " . Artisan::output() . " | " . $e->getMessage());
        } catch (\Exception $e2) {
            return response()->json("Failed: " . $e->getMessage());
        }
    }
});

/**
 * 2025-03-24
 * + 1 bulan = 2025-04-24
 * ubah tanggal menjadi tanggal toleransi = 2025-04-27
 * =======================================
 * tgl pakai = 2025-02-24
 * tgl akhir = 2025-03-27
 * tgl hari ini = 2025-03-26
 *
 * (tgl_akhir <= tgl hari ini)
 * tgl akhir <= hari ini : bulan llalu
 *
 */

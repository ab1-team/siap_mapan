<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah menu "Reversal Piutang" di bawah parent "Transaksi".
     *
     * Menu ini digunakan untuk mengoreksi transaksi piutang tunggakan
     * yang terlanjur dibuat oleh SystemController::dataset (generate
     * tunggakan) padahal pelanggan sebenarnya sudah membayar via
     * transfer sebelum generate berjalan. Admin dapat memilih bulan
     * yang akan di-reversal, sistem akan menghapus transaksi jurnal
     * piutang (D 1.1.03.01 / K 4.1.01.02|03|04) untuk usage_id terkait
     * dan menghitung ulang saldo Amount bulan tersebut.
     *
     * Idempotent: jika menu dengan link yang sama sudah ada, lewati.
     */
    public function up(): void
    {
        $exists = DB::table('menu')
            ->where('link', '/transactions/reversal_piutang')
            ->exists();

        if ($exists) {
            return;
        }

        // Cari parent "Transaksi" - fallback ke id=17 (parent menu Transaksi default)
        $parentId = DB::table('menu')
            ->where('title', 'Transaksi')
            ->where('parent_id', 0)
            ->value('id') ?? 17;

        DB::table('menu')->insert([
            'parent_id' => $parentId,
            'title' => 'Reversal Piutang',
            'link' => '/transactions/reversal_piutang',
            'icon' => 'collapse-item',
            'status' => 'A',
        ]);
    }

    /**
     * Rollback: hapus menu yang baru ditambahkan.
     * Idempotent & aman.
     */
    public function down(): void
    {
        DB::table('menu')
            ->where('link', '/transactions/reversal_piutang')
            ->delete();
    }
};
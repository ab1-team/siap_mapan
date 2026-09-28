<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index komposit untuk mempercepat query DashboardController::index() / chart() / tunggakan().
 *
 * Tujuan:
 *  - installations(business_id, status)        -> count Installation, UsageCount EXISTS, Tunggakan
 *  - usages(business_id, status, tgl_akhir)    -> count Tagihan, EXISTS UsageCount
 *  - amounts(account_id, tahun, bulan)         -> agregasi chart keuangan
 *  - accounts(business_id, lev1)               -> filter akun pendapatan/beban
 *
 * Idempotent: pakai hasIndex() supaya aman di-run ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        // installations
        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (!$this->hasIndex('installations', 'idx_installations_biz_status')) {
                    $table->index(['business_id', 'status'], 'idx_installations_biz_status');
                }
                if (!$this->hasIndex('installations', 'idx_installations_kategori_biz')) {
                    $table->index(['kategori', 'business_id', 'status'], 'idx_installations_kategori_biz');
                }
            });
        }

        // usages
        if (Schema::hasTable('usages')) {
            Schema::table('usages', function (Blueprint $table) {
                if (!$this->hasIndex('usages', 'idx_usages_biz_status_tgl')) {
                    $table->index(['business_id', 'status', 'tgl_akhir'], 'idx_usages_biz_status_tgl');
                }
                if (!$this->hasIndex('usages', 'idx_usages_inst_status_tgl')) {
                    $table->index(['id_instalasi', 'status', 'tgl_akhir'], 'idx_usages_inst_status_tgl');
                }
            });
        }

        // amounts
        if (Schema::hasTable('amounts')) {
            Schema::table('amounts', function (Blueprint $table) {
                if (!$this->hasIndex('amounts', 'idx_amounts_account_tahun_bulan')) {
                    $table->index(['account_id', 'tahun', 'bulan'], 'idx_amounts_account_tahun_bulan');
                }
            });
        }

        // accounts
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!$this->hasIndex('accounts', 'idx_accounts_biz_lev1')) {
                    $table->index(['business_id', 'lev1'], 'idx_accounts_biz_lev1');
                }
            });
        }
    }

    public function down(): void
    {
        $drop = function (string $table, string $index) {
            if (Schema::hasTable($table) && $this->hasIndex($table, $index)) {
                Schema::table($table, function (Blueprint $t) use ($index) {
                    $t->dropIndex($index);
                });
            }
        };

        $drop('installations', 'idx_installations_biz_status');
        $drop('installations', 'idx_installations_kategori_biz');
        $drop('usages',        'idx_usages_biz_status_tgl');
        $drop('usages',        'idx_usages_inst_status_tgl');
        $drop('amounts',       'idx_amounts_account_tahun_bulan');
        $drop('accounts',      'idx_accounts_biz_lev1');
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($table);
        return array_key_exists($indexName, $indexes);
    }
};
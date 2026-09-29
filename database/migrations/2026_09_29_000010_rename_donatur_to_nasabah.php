<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename penuh: donatur -> nasabah (tabel, kolom, sequence, constraint).
 * Postgres otomatis memperbarui FK/index saat RENAME; nama constraint
 * dirapikan eksplisit ke pola *_nasabah_*.
 * Permission (donatur-list dst) ditangani migrasi terpisah tanpa FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) kolom pada tabel lain (rename kolom memperbarui FK transaksi.donatur_id otomatis)
        Schema::table('transaksi', function ($table) {
            $table->renameColumn('donatur_id', 'nasabah_id');
        });

        // 2) kolom report_harian (8 kolom) — tanpa FK, aman
        foreach ([
            'renku_donatur_lama' => 'renku_nasabah_lama',
            'renku_donatur_baru' => 'renku_nasabah_baru',
            'realisasi_donatur_lama' => 'realisasi_nasabah_lama',
            'realisasi_donatur_baru' => 'realisasi_nasabah_baru',
            'fu_donatur_lama' => 'fu_nasabah_lama',
            'fu_donatur_baru' => 'fu_nasabah_baru',
            'deal_donatur_lama' => 'deal_nasabah_lama',
            'deal_donatur_baru' => 'deal_nasabah_baru',
        ] as $from => $to) {
            Schema::table('report_harian', function ($table) use ($from, $to) {
                $table->renameColumn($from, $to);
            });
        }

        // 3) rename tabel (FK/index internal otomatis mengikuti)
        Schema::rename('donatur', 'nasabah');

        // 4) rapikan nama objek -> pola *_nasabah_*
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT donatur_pkey TO nasabah_pkey');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT donatur_pegawai_id_foreign TO nasabah_pegawai_id_foreign');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT donatur_pegawai_self_id_foreign TO nasabah_pegawai_self_id_foreign');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT donatur_pegawai_self_id_unique TO nasabah_pegawai_self_id_unique');
        DB::statement('ALTER INDEX IF EXISTS donatur_pegawai_self_id_unique RENAME TO nasabah_pegawai_self_id_unique');
        DB::statement('ALTER SEQUENCE IF EXISTS donatur_id_seq RENAME TO nasabah_id_seq');
    }

    public function down(): void
    {
        DB::statement('ALTER SEQUENCE IF EXISTS nasabah_id_seq RENAME TO donatur_id_seq');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT nasabah_pkey TO donatur_pkey');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT nasabah_pegawai_id_foreign TO donatur_pegawai_id_foreign');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT nasabah_pegawai_self_id_foreign TO donatur_pegawai_self_id_foreign');
        DB::statement('ALTER TABLE nasabah RENAME CONSTRAINT nasabah_pegawai_self_id_unique TO donatur_pegawai_self_id_unique');
        DB::statement('ALTER INDEX IF EXISTS nasabah_pegawai_self_id_unique RENAME TO donatur_pegawai_self_id_unique');

        Schema::rename('nasabah', 'donatur');

        Schema::table('transaksi', function ($table) {
            $table->renameColumn('nasabah_id', 'donatur_id');
        });

        foreach ([
            'renku_nasabah_lama' => 'renku_donatur_lama',
            'renku_nasabah_baru' => 'renku_donatur_baru',
            'realisasi_nasabah_lama' => 'realisasi_donatur_lama',
            'realisasi_nasabah_baru' => 'realisasi_donatur_baru',
            'fu_nasabah_lama' => 'fu_donatur_lama',
            'fu_nasabah_baru' => 'fu_donatur_baru',
            'deal_nasabah_lama' => 'deal_donatur_lama',
            'deal_nasabah_baru' => 'deal_donatur_baru',
        ] as $from => $to) {
            Schema::table('report_harian', function ($table) use ($from, $to) {
                $table->renameColumn($from, $to);
            });
        }
    }
};

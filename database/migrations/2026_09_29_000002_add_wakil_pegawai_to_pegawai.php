<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * R4: relasi pewakilan laporan.
 * Kolom pegawai.wakil_pegawai_id = penghimpun/pihak yang mewakili pelaporan
 * ketika pegawai tidak aktif. Laporan tetap menghitung nasabah milik pegawai
 * asli (kepemilikan tidak berpindah); kolom ini hanya untuk atribusi wakil.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->unsignedBigInteger('wakil_pegawai_id')->nullable()->after('jabatan');
            $table->foreign('wakil_pegawai_id')->references('id')->on('pegawai')->onDelete('set null');
        });

        DB::statement('ALTER TABLE pegawai ADD CONSTRAINT pegawai_wakil_not_self CHECK (wakil_pegawai_id IS NULL OR wakil_pegawai_id <> id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE pegawai DROP CONSTRAINT IF EXISTS pegawai_wakil_not_self');
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropForeign(['wakil_pegawai_id']);
            $table->dropColumn('wakil_pegawai_id');
        });
    }
};

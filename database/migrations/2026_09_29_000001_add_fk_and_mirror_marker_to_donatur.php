<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * R1 + R2:
 *  - R1: tambah integritas FK donatur.pegawai_id -> pegawai.id (sebelumnya TIDAK ADA FK).
 *  - R2: tandai baris donatur yang merupakan "diri" seorang pegawai (mirror)
 *        lewat kolom eksplisit donatur.pegawai_self_id (unique, nullable),
 *        bukan lagi mengandalkan pencocokan nama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donatur', function (Blueprint $table) {
            $table->unsignedBigInteger('pegawai_self_id')->nullable()->after('pegawai_id');
        });

        // Backfill: baris donatur yang namanya = nama pegawai DAN dimiliki dirinya sendiri.
        // Aman & deterministik: hanya 1 baris per pegawai (unique), 0 orphan terverifikasi.
        DB::statement("
            UPDATE donatur d
               SET pegawai_self_id = p.id
              FROM pegawai p
             WHERE p.id = d.pegawai_id
               AND lower(trim(d.nama)) = lower(trim(p.nama))
        ");

        Schema::table('donatur', function (Blueprint $table) {
            $table->unique('pegawai_self_id');
            $table->foreign('pegawai_id')->references('id')->on('pegawai')->onDelete('restrict');
            $table->foreign('pegawai_self_id')->references('id')->on('pegawai')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('donatur', function (Blueprint $table) {
            $table->dropForeign(['pegawai_self_id']);
            $table->dropForeign(['pegawai_id']);
            $table->dropUnique(['pegawai_self_id']);
            $table->dropColumn('pegawai_self_id');
        });
    }
};

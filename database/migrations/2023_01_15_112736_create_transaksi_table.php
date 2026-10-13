<?php

use App\Models\File;
use App\Models\Pegawai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Pegawai::class);
            // NOTE: was foreignIdFor(Donatur::class) — Donatur was renamed to
            // Nasabah (see 2026_09_29_000010), so the class no longer exists.
            // foreignIdFor() only creates the column (no FK), so the explicit
            // column name keeps the original schema identical.
            $table->foreignId('donatur_id');
            $table->foreignIdFor(File::class)->nullable();
            $table->string('jenis_transaksi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
};

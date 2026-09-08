<?php

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
        Schema::create('report_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->integer('pegawai_id');
            $table->integer('renku_donatur_lama');
            $table->integer('renku_donatur_baru');
            $table->integer('realisasi_donatur_lama');
            $table->integer('realisasi_donatur_baru');
            $table->integer('fu_donatur_lama');
            $table->integer('fu_donatur_baru');
            $table->integer('deal_donatur_lama');
            $table->integer('deal_donatur_baru');
            // $table->string('jenis_akad');
            $table->jsonb('jenis_akad')->nullable();
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
        Schema::dropIfExists('report_harian');
    }
};

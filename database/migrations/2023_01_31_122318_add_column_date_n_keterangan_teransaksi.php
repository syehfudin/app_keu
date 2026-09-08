<?php

use Illuminate\Database\Migrations\Migration;
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
        Schema::table('transaksi', function ($table) {
            $table->date('tanggal')->after('id')->nullable();
            $table->text('keterangan')->after('jenis_transaksi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaksi', function ($table) {
            $table->dropColumn('tanggal');
            $table->dropColumn('keterangan');
        });
    }
};

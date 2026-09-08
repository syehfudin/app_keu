<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaksi';

    protected $fillable = [
        'tanggal', 'pegawai_id', 'donatur_id', 'file_id', 'jenis_transaksi', 'keterangan',
    ];
}

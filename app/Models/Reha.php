<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reha extends Model
{
    use HasFactory;
    protected $table = 'report_harian';

    protected $guarded = ['id'];

    protected $fillable = [
        'tanggal', 'pegawai_id', 'renku_donatur_lama', 'renku_donatur_baru', 'realisasi_donatur_lama', 'realisasi_donatur_baru', 'fu_donatur_lama', 'fu_donatur_baru', 'deal_donatur_lama', 'deal_donatur_baru', 'jenis_akad'
    ];
}

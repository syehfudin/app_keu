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
        'tanggal', 'pegawai_id', 'renku_nasabah_lama', 'renku_nasabah_baru', 'realisasi_nasabah_lama', 'realisasi_nasabah_baru', 'fu_nasabah_lama', 'fu_nasabah_baru', 'deal_nasabah_lama', 'deal_nasabah_baru', 'jenis_akad'
    ];
}

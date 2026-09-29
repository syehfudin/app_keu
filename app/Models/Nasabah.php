<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    use HasFactory;

    protected $table = 'nasabah';

    protected $gurarded = 'id';

    protected $fillable = [
        'pegawai_id', 'pegawai_self_id', 'nama', 'alamat', 'no_telepon', 'pekerjaan',
    ];

    public function Pegawai()
    {
        return $this->hasMany(Nasabah::class, 'id', 'pegawai_id');
    }

    /**
     * R2: relasi pemilik (penghimpun penanggung nasabah).
     */
    public function owner()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    /**
     * R2: relasi "diri" — terisi bila baris nasabah ini adalah mirror
     * dari seorang pegawai (penghimpun juga terdaftar sebagai nasabah).
     */
    public function selfPegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_self_id', 'id');
    }
}

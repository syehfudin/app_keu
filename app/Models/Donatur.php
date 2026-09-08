<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donatur extends Model
{
    use HasFactory;

    protected $table = 'donatur';

    protected $gurarded = 'id';

    protected $fillable = [
        'pegawai_id', 'nama', 'alamat', 'no_telepon', 'pekerjaan',
    ];

    public function Pegawai()
    {
        return $this->hasMany(Donatur::class, 'id', 'pegawai_id');
    }
}

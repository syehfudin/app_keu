<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pegawai';

    protected $gurarded = 'id';

    protected $fillable = [
        'jabatan', 'nip', 'nama', 'alamat', 'no_telepon', 'default',
    ];

    public function Donatur()
    {
        return $this->belongsTo(Donatur::class);
    }

    public function getRelawan($pegawai_id)
    {
        $user = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
                ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->join('korel as k', function ($join) {
                    $join->on('p.id', '=', 'k.bawahan_id');
                    $join->orOn('p.id', '=', 'k.kepala_id', 'or');
                })
                ->where('k.kepala_id', $pegawai_id)
                ->select([
                    'p.id',
                    'p.nama',
                ])
                ->get();

        return $user;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Korel extends Model
{
    use HasFactory;

    protected $table = 'korel';

    protected $gurarded = 'id';

    protected $fillable = [
        'kepala_id', 'bawahan_id',
    ];
}

<?php

namespace App\Traits;

use App\Models\Korel;
use Auth;

trait HasHierarchy
{
    protected function getAccessiblePegawaiIds()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;

        if (in_array($role, ['admin', 'manager'])) {
            return null;
        }

        if ($role == 'supervisor') {
            $bawahanIds = Korel::where('kepala_id', $pegawai_id)
                ->pluck('bawahan_id')
                ->toArray();
            $ids = array_unique(array_merge([$pegawai_id], $bawahanIds));
            return $ids;
        }

        return [$pegawai_id];
    }

    protected function isPenghimpun()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        return in_array($role, ['penghimpun', 'supervisor']);
    }
}

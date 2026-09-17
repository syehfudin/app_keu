<?php

namespace App\Traits;

use App\Models\Korel;
use Auth;
use DB;

trait HasHierarchy
{
    /**
     * READ-SCOPE: pegawai IDs yang datanya boleh dilihat user.
     *
     * - admin / direktur / dirops : null (= semua)
     * - lainnya                   : {diri} ∪ seluruh subtree bawahan (recursive, multi-level)
     *
     * Roll-up report otomatis: nasabah milik Manager/GM/Direktur/DirOps
     * tetap miliknya (donatur.pegawai_id), dan dihitung di laporan
     * atasan wilayahnya lewat subtree traversal.
     */
    protected function getAccessiblePegawaiIds()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;

        if (in_array($role, ['admin', 'direktur', 'dirops'])) {
            return null;
        }

        // Penghimpun tanpa bawahan tetap boleh punya subtree (dirinya saja,
        // tapi tetap lewat recursive agar konsisten dan cycle-safe).
        return $this->getSubtreeIds($pegawai_id);
    }

    /**
     * INPUT-SCOPE: pegawai IDs yang boleh dijadikan pegawai_id transaksi
     * oleh user yang login.
     *
     * - penghimpun / manager / gm / direktur / dirops : [diri sendiri]
     * - supervisor                                    : {diri} ∪ subtree bawahan
     *   (supervisor boleh memfasilitasi input atas nama bawahan)
     * - admin                                         : null (= semua)
     */
    protected function getInputScopeIds()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;

        if ($role == 'admin') {
            return null;
        }

        if ($role == 'supervisor') {
            return $this->getSubtreeIds($pegawai_id);
        }

        return [$pegawai_id];
    }

    /**
     * Recursive subtree via PostgreSQL WITH RECURSIVE.
     * Return array berisi $pegawai_id + semua bawahan di semua level.
     * Cycle-safe (guard pada recursive term) dan cycle-guard via max depth.
     */
    protected function getSubtreeIds($pegawai_id)
    {
        $ids = DB::select("
            WITH RECURSIVE subtree AS (
                SELECT :root::bigint AS id, 0 AS depth
                UNION ALL
                SELECT k.bawahan_id, s.depth + 1
                FROM korel k
                JOIN subtree s ON s.id = k.kepala_id
                WHERE s.depth < 20
            )
            SELECT DISTINCT id FROM subtree
        ", ['root' => $pegawai_id]);

        return array_map(fn ($row) => (int) $row->id, $ids);
    }

    /**
     * CYCLE GUARD: apakah menambah relasi $kepala -> $bawahan membentuk siklus?
     * Siklus terjadi jika $kepala berada di dalam subtree $bawahan
     * (artinya kepala adalah bawahan dari bawahan — dilarang).
     * Juga tolak self-loop (kepala = bawahan).
     */
    protected function wouldCreateCycle($kepala_id, $bawahan_id)
    {
        if ((int) $kepala_id === (int) $bawahan_id) {
            return true;
        }

        $subtree = $this->getSubtreeIds($bawahan_id);

        return in_array((int) $kepala_id, $subtree, true);
    }

    /**
     * Kompatibilitas eksisting: role yang "juga penghimpun" (punya nasabah sendiri).
     * Dalam struktur baru SEMUA jabatan (termasuk GM/Manager/Direktur/DirOps)
     * punya nasabah sendiri; hanya dipakai untuk membedakan konteks input.
     */
    protected function isPenghimpun()
    {
        $role = strtolower(Auth::user()->roles[0]->name);

        return in_array($role, ['penghimpun', 'supervisor', 'manager', 'gm',
            'general manager', 'direktur', 'dirops', 'dir. ops', 'direktur ops', 'direktur operasional']);
    }
}
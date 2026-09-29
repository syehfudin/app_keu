<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * R3: satukan sumber jabatan.
 * roles adalah sumber kebenaran; pegawai.jabatan disinkronkan darinya
 * (sebelumnya 10 baris tidak konsisten: mis. jabatan 'Penghimpun' tapi role 'Supervisor').
 * Hanya menyentuh pegawai yang punya user (login).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE pegawai p
               SET jabatan = r.name
              FROM users u
              JOIN model_has_roles mhr ON mhr.model_id = u.id
              JOIN roles r ON r.id = mhr.role_id
             WHERE u.pegawai_id = p.id
        ");
    }

    public function down(): void
    {
        // Data sebelumnya disimpan di backup dir (jabatan-before.txt) sebelum migrasi.
        // Tidak ada rollback data otomatis.
    }
};

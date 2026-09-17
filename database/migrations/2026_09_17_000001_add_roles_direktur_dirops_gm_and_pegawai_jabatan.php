<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== Fase 1a: 3 role baru =====
        $newRoles = ['Direktur', 'DirOps', 'General Manager'];
        foreach ($newRoles as $role) {
            if (! DB::table('roles')->where('name', $role)->exists()) {
                DB::table('roles')->insert([
                    'name' => $role,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ===== Fase 1b: kolom jabatan di pegawai =====
        if (! Schema::hasColumn('pegawai', 'jabatan')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->string('jabatan')->nullable()->default(null)->after('nama');
            });
        }
        // Backfill jabatan berdasarkan role user yang terhubung
        DB::statement("
            UPDATE pegawai p
            SET jabatan = r.name
            FROM users u
            JOIN model_has_roles mhr ON u.id = mhr.model_id
            JOIN roles r ON r.id = mhr.role_id
            WHERE p.id = u.pegawai_id
              AND (p.jabatan IS NULL OR p.jabatan = '')
        ");

        // ===== Fase 1a: permission matrix untuk 3 role baru =====
        // Direktur & DirOps: monitoring semua modul (list-only)
        $monitorPermissions = [
            'pegawai-list', 'donatur-list', 'pekerjaan-list', 'program-list',
            'transaksi-list', 'transaksi-create', 'transaksi-edit',
            'setoran-list', 'setoran-create', 'setoran-edit',
            'korel-list', 'reha-list', 'tunai-list', 'import-list',
        ];
        // GM: monitoring + operasional wilayah (transaksi input/edit)
        $gmPermissions = array_merge($monitorPermissions, [
            'transaksi-create', 'transaksi-edit',
            'setoran-create', 'setoran-edit',
        ]);

        $attach = function (string $roleName, array $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            foreach ($perms as $permName) {
                $permId = DB::table('permissions')->where('name', $permName)->value('id');
                if ($permId && ! DB::table('role_has_permissions')
                    ->where('role_id', $roleId)->where('permission_id', $permId)->exists()) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId,
                    ]);
                }
            }
        };

        $attach('Direktur', $monitorPermissions);
        $attach('DirOps', $monitorPermissions);
        $attach('General Manager', $gmPermissions);
    }

    public function down(): void
    {
        foreach (['Direktur', 'DirOps', 'General Manager'] as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if ($roleId) {
                DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
                DB::table('model_has_roles')->where('role_id', $roleId)->delete();
                DB::table('roles')->where('id', $roleId)->delete();
            }
        }
        if (Schema::hasColumn('pegawai', 'jabatan')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }
};
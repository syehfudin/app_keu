<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // 'role-list',
            // 'role-create',
            // 'role-edit',
            // 'role-delete',
            // 'pegawai-list',
            // 'pegawai-create',
            // 'pegawai-edit',
            // 'pegawai-delete',
            // 'donatur-list',
            // 'donatur-create',
            // 'donatur-edit',
            // 'donatur-delete',
            // 'pekerjaan-list',
            // 'pekerjaan-create',
            // 'pekerjaan-edit',
            // 'pekerjaan-delete',
            // 'program-list',
            // 'program-create',
            // 'program-edit',
            // 'program-delete',
            // 'transaksi-list',
            // 'transaksi-create',
            // 'transaksi-edit',
            // 'transaksi-delete',
            // 'setoran-list',
            // 'setoran-create',
            // 'setoran-edit',
            // 'setoran-delete',
            'reha-list',
            'reha-create',
            'reha-edit',
            'reha-delete',
            // 'korel-list',
            // 'korel-create',
            // 'korel-edit',
            // 'korel-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}

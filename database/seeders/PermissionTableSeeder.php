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
        // Permissions referenced by controller middleware (permission:*)
        // and by @can(...) gates in resources/views.
        $modules = [
            'role',
            'pegawai',
            'nasabah',
            'pekerjaan',
            'program',
            'product',
            'transaksi',
            'setoran',
            'korel',
            'reha',
        ];

        $permissions = [];
        foreach ($modules as $module) {
            foreach (['list', 'create', 'edit', 'delete'] as $action) {
                $permissions[] = "{$module}-{$action}";
            }
        }

        // Modules that only expose a subset of actions
        $permissions[] = 'import-list';
        $permissions[] = 'import-create';
        $permissions[] = 'tunai-list';

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}

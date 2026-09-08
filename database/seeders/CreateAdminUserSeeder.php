<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Admin Seeder
        $pegawai = Pegawai::create([
            'nip' => '007',
            'nama' => 'admin',
            'alamat' => '',
        ]);

        $user = User::create([
            'pegawai_id' => $pegawai->id,
            'username' => 'LaravelTuts',
            // 'email'     => 'admin@laraveltuts.com',
            'password' => bcrypt('password'),
        ]);

        $role = Role::create(['name' => 'Admin']);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}

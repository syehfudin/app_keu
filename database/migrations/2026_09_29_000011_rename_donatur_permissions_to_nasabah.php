<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rename nama permission donatur-* -> nasabah-*.
 * Tanpa perubahan kunci id -> aman terhadap FK role_has_permissions.
 */
return new class extends Migration
{
    private array $map = [
        'donatur-list' => 'nasabah-list',
        'donatur-create' => 'nasabah-create',
        'donatur-edit' => 'nasabah-edit',
        'donatur-delete' => 'nasabah-delete',
    ];

    public function up(): void
    {
        foreach ($this->map as $from => $to) {
            DB::table('permissions')->where('name', $from)->update(['name' => $to]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $from => $to) {
            DB::table('permissions')->where('name', $to)->update(['name' => $from]);
        }
    }
};

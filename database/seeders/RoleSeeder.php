<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'coordinador', 'profesor', 'estudiante'];

        foreach ($roles as $name) {
            DB::table('roles')->insertOrIgnore([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Roles creados: ' . implode(', ', $roles));
    }
}

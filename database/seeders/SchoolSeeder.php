<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            [
                'name'       => 'Institución Educativa Juan Bosco Sarmiento',
                'nit'        => '890901234-5',
                'resolution' => 'Res. 001245 de 2003',
                'phone'      => '6017892345',
                'address'    => 'Calle 45 # 23-15, Bogotá D.C.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Colegio Nacional San Martín',
                'nit'        => '890901567-8',
                'resolution' => 'Res. 002318 de 2005',
                'phone'      => '6014567890',
                'address'    => 'Carrera 12 # 56-30, Medellín',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Institución Educativa Técnica La Paz',
                'nit'        => '890901890-1',
                'resolution' => 'Res. 003456 de 2008',
                'phone'      => '6057891234',
                'address'    => 'Avenida 30 # 10-45, Cali',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($schools as $school) {
            DB::table('schools')->insertOrIgnore($school);
        }

        $this->command->info('✓ ' . count($schools) . ' colegios creados.');
    }
}

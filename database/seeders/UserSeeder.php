<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId       = DB::table('roles')->where('name', 'admin')->value('id');
        $coordRoleId       = DB::table('roles')->where('name', 'coordinador')->value('id');
        $teacherRoleId     = DB::table('roles')->where('name', 'profesor')->value('id');
        $studentRoleId     = DB::table('roles')->where('name', 'estudiante')->value('id');

        $school1 = DB::table('schools')->where('nit', '890901234-5')->value('id');
        $school2 = DB::table('schools')->where('nit', '890901567-8')->value('id');
        $school3 = DB::table('schools')->where('nit', '890901890-1')->value('id');

        $now = now();

        // ── Admin ─────────────────────────────────────────────────────────
        $admin = $this->upsertUser([
            'first_name' => 'Superadmin',
            'last_name'  => 'Sistema',
            'username'   => 'admin',
            'email'      => 'admin@gestion.edu.co',
            'password'   => Hash::make('Admin2024*'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->attachRole($admin, $adminRoleId);

        // ── Coordinadores ─────────────────────────────────────────────────
        $coordinadores = [
            ['first_name' => 'coordinador',   'last_name' => 'coordinador',   'username' => 'coordinador',  'email' => 'coordinador@gestion.edu.co',  'school' => $school1],
            ['first_name' => 'Liliana',   'last_name' => 'Torres',    'username' => 'ltorres',      'email' => 'ltorres@gestion.edu.co',       'school' => $school2],
            ['first_name' => 'Héctor',    'last_name' => 'Bermúdez',  'username' => 'hbermudez',    'email' => 'hbermudez@gestion.edu.co',     'school' => $school3],
        ];

        foreach ($coordinadores as $c) {
            $user = $this->upsertUser([
                'first_name' => $c['first_name'],
                'last_name'  => $c['last_name'],
                'username'   => $c['username'],
                'email'      => $c['email'],
                'password'   => Hash::make('Coord2024*'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->attachRole($user, $coordRoleId);
            $this->attachSchool($user, $c['school']);
        }

        // ── Profesores ────────────────────────────────────────────────────
        $profesores = [
            // Colegio 1
            ['first_name' => 'profesor',   'last_name' => 'profesor',   'username' => 'profesor',  'email' => 'profesor@gestion.edu.co',  'school' => $school1],
            ['first_name' => 'Diana',      'last_name' => 'Ríos',        'username' => 'drios',        'email' => 'drios@gestion.edu.co',         'school' => $school1],
            ['first_name' => 'Mauricio',   'last_name' => 'Ospina',      'username' => 'mospina',      'email' => 'mospina@gestion.edu.co',       'school' => $school1],
            ['first_name' => 'Patricia',   'last_name' => 'Vargas',      'username' => 'pvargas',      'email' => 'pvargas@gestion.edu.co',       'school' => $school1],
            ['first_name' => 'Rodrigo',    'last_name' => 'Castillo',    'username' => 'rcastillo',    'email' => 'rcastillo@gestion.edu.co',     'school' => $school1],
            // Colegio 2
            ['first_name' => 'Adriana',    'last_name' => 'Morales',     'username' => 'amorales',     'email' => 'amorales@gestion.edu.co',      'school' => $school2],
            ['first_name' => 'Fernando',   'last_name' => 'Salcedo',     'username' => 'fsalcedo',     'email' => 'fsalcedo@gestion.edu.co',      'school' => $school2],
            ['first_name' => 'Gloria',     'last_name' => 'Herrera',     'username' => 'gherrera',     'email' => 'gherrera@gestion.edu.co',      'school' => $school2],
            ['first_name' => 'Iván',       'last_name' => 'Pedroza',     'username' => 'ipedroza',     'email' => 'ipedroza@gestion.edu.co',      'school' => $school2],
            ['first_name' => 'Juliana',    'last_name' => 'Acosta',      'username' => 'jacosta',      'email' => 'jacosta@gestion.edu.co',       'school' => $school2],
            // Colegio 3
            ['first_name' => 'Kevin',      'last_name' => 'Serrano',     'username' => 'kserrano',     'email' => 'kserrano@gestion.edu.co',      'school' => $school3],
            ['first_name' => 'Lorena',     'last_name' => 'Parra',       'username' => 'lparra',       'email' => 'lparra@gestion.edu.co',        'school' => $school3],
            ['first_name' => 'Miguel',     'last_name' => 'Quintero',    'username' => 'mquintero',    'email' => 'mquintero@gestion.edu.co',     'school' => $school3],
        ];

        foreach ($profesores as $p) {
            $user = $this->upsertUser([
                'first_name' => $p['first_name'],
                'last_name'  => $p['last_name'],
                'username'   => $p['username'],
                'email'      => $p['email'],
                'password'   => Hash::make('Profe2024*'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->attachRole($user, $teacherRoleId);
            $this->attachSchool($user, $p['school']);
        }

        // ── Estudiantes ───────────────────────────────────────────────────
        $estudiantes = [
            ['first_name' => 'Alejandro',  'last_name' => 'Méndez',   'username' => 'amendez',   'email' => 'amendez@gestion.edu.co',   'school' => $school1],
            ['first_name' => 'Valentina',  'last_name' => 'Cruz',     'username' => 'vcruz',     'email' => 'vcruz@gestion.edu.co',     'school' => $school1],
            ['first_name' => 'Santiago',   'last_name' => 'Díaz',     'username' => 'sdiaz',     'email' => 'sdiaz@gestion.edu.co',     'school' => $school1],
            ['first_name' => 'Isabella',   'last_name' => 'Pineda',   'username' => 'ipineda',   'email' => 'ipineda@gestion.edu.co',   'school' => $school2],
            ['first_name' => 'Sebastián',  'last_name' => 'Molina',   'username' => 'smolina',   'email' => 'smolina@gestion.edu.co',   'school' => $school2],
            ['first_name' => 'Camila',     'last_name' => 'Restrepo', 'username' => 'crestrepo', 'email' => 'crestrepo@gestion.edu.co', 'school' => $school2],
            ['first_name' => 'Nicolás',    'last_name' => 'García',   'username' => 'ngarcia',   'email' => 'ngarcia@gestion.edu.co',   'school' => $school3],
            ['first_name' => 'Mariana',    'last_name' => 'López',    'username' => 'mlopez',    'email' => 'mlopez@gestion.edu.co',    'school' => $school3],
        ];

        foreach ($estudiantes as $e) {
            $user = $this->upsertUser([
                'first_name' => $e['first_name'],
                'last_name'  => $e['last_name'],
                'username'   => $e['username'],
                'email'      => $e['email'],
                'password'   => Hash::make('Estu2024*'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->attachRole($user, $studentRoleId);
            $this->attachSchool($user, $e['school']);
        }

        $total = 1 + count($coordinadores) + count($profesores) + count($estudiantes);
        $this->command->info("✓ {$total} usuarios creados (1 admin, " . count($coordinadores) . " coordinadores, " . count($profesores) . " profesores, " . count($estudiantes) . " estudiantes).");
        $this->command->info('  Contraseñas: admin → Admin2024* | coordinador → Coord2024* | profesor → Profe2024* | estudiante → Estu2024*');
    }

    private function upsertUser(array $data): int
    {
        $existing = DB::table('users')
            ->where('email', $data['email'])
            ->orWhere('username', $data['username'])
            ->value('id');
        if ($existing) return $existing;

        return DB::table('users')->insertGetId($data);
    }

    private function attachRole(int $userId, int $roleId): void
    {
        DB::table('roles_user')->insertOrIgnore([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachSchool(int $userId, ?int $schoolId): void
    {
        if (!$schoolId) return;
        DB::table('user_school')->insertOrIgnore([
            'user_id'    => $userId,
            'school_id'  => $schoolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

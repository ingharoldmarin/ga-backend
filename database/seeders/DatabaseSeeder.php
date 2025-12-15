<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $coordinatorRole = Role::firstOrCreate(['name' => 'coordinator']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // Usuarios de prueba
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'password' => Hash::make('password')
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $coordinator = User::firstOrCreate(
            ['email' => 'coordinator@example.com'],
            [
                'first_name' => 'Coord',
                'last_name' => 'User',
                'username' => 'coordinator',
                'password' => Hash::make('password')
            ]
        );
        $coordinator->roles()->syncWithoutDetaching([$coordinatorRole->id]);

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'first_name' => 'Teacher',
                'last_name' => 'User',
                'username' => 'teacher',
                'password' => Hash::make('password')
            ]
        );
        $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

        $student = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'first_name' => 'Student',
                'last_name' => 'User',
                'username' => 'student',
                'password' => Hash::make('password')
            ]
        );
        $student->roles()->syncWithoutDetaching([$studentRole->id]);
    }
}

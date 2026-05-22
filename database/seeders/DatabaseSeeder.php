<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Orden de ejecución:
     * 1. Roles
     * 2. Colegios
     * 3. Usuarios (admin, coordinadores, profesores, estudiantes)
     * 4. Grados y Materias
     * 5. Elementos curriculares (topics, components, standards, competences, affirmations, evidences)
     * 6. Mallas curriculares (curriculum_grid + pivots)
     * 7. Asignaciones (mallas a profesores, teacher_subject)
     * 8. Cronogramas semanales y observaciones de ejemplo
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('════════════════════════════════════════');
        $this->command->info('  GestiónAcadémica — Seed completo');
        $this->command->info('════════════════════════════════════════');

        $this->call([
            RoleSeeder::class,
            SchoolSeeder::class,
            UserSeeder::class,
            GradeSubjectSeeder::class,
            CurriculumItemsSeeder::class,
            CurriculumGridSeeder::class,
            AssignmentSeeder::class,
            WeekScheduleSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('════════════════════════════════════════');
        $this->command->info('  ✓ Seed completado exitosamente.');
        $this->command->info('');
        $this->command->info('  CREDENCIALES DE ACCESO:');
        $this->command->info('  ┌──────────────────┬─────────────────────────────────┬─────────────┐');
        $this->command->info('  │ Rol              │ Email                           │ Contraseña  │');
        $this->command->info('  ├──────────────────┼─────────────────────────────────┼─────────────┤');
        $this->command->info('  │ Admin            │ admin@gestion.edu.co            │ Admin2024*  │');
        $this->command->info('  │ Coordinador      │ coordinador@gestion.edu.co      │ Coord2024*  │');
        $this->command->info('  │ Profesor         │ profesor@gestion.edu.co         │ Profe2024*  │');
        $this->command->info('  │ Estudiante       │ amendez@gestion.edu.co          │ Estu2024*   │');
        $this->command->info('  └──────────────────┴─────────────────────────────────┴─────────────┘');
        $this->command->info('════════════════════════════════════════');
        $this->command->info('');
    }
}

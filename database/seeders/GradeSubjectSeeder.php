<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Grados ────────────────────────────────────────────────────────
        $grades = [
            ['name' => 'Transición',  'description' => 'Grado Transición (Preescolar)'],
            ['name' => 'Primero',     'description' => 'Grado 1° de Primaria'],
            ['name' => 'Segundo',     'description' => 'Grado 2° de Primaria'],
            ['name' => 'Tercero',     'description' => 'Grado 3° de Primaria'],
            ['name' => 'Cuarto',      'description' => 'Grado 4° de Primaria'],
            ['name' => 'Quinto',      'description' => 'Grado 5° de Primaria'],
            ['name' => 'Sexto',       'description' => 'Grado 6° de Básica Secundaria'],
            ['name' => 'Séptimo',     'description' => 'Grado 7° de Básica Secundaria'],
            ['name' => 'Octavo',      'description' => 'Grado 8° de Básica Secundaria'],
            ['name' => 'Noveno',      'description' => 'Grado 9° de Básica Secundaria'],
            ['name' => 'Décimo',      'description' => 'Grado 10° de Media Vocacional'],
            ['name' => 'Undécimo',    'description' => 'Grado 11° de Media Vocacional'],
        ];

        foreach ($grades as $g) {
            DB::table('grade')->insertOrIgnore(array_merge($g, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── Áreas / Materias ──────────────────────────────────────────────
        $subjects = [
            ['name' => 'Matemáticas',                 'description' => 'Área de Matemáticas — pensamiento numérico, geométrico, variacional, métrico y aleatorio'],
            ['name' => 'Lengua Castellana',            'description' => 'Área de Lenguaje — comprensión lectora, producción textual y comunicación'],
            ['name' => 'Ciencias Naturales',           'description' => 'Área de Ciencias Naturales — biología, química y física básica'],
            ['name' => 'Ciencias Sociales',            'description' => 'Área de Ciencias Sociales — historia, geografía y competencias ciudadanas'],
            ['name' => 'Inglés',                       'description' => 'Área de Inglés — comunicación oral y escrita en lengua extranjera'],
            ['name' => 'Educación Física',             'description' => 'Área de Educación Física — desarrollo corporal, deportes y vida saludable'],
            ['name' => 'Artes',                        'description' => 'Área de Educación Artística — expresión plástica, música y teatro'],
            ['name' => 'Tecnología e Informática',     'description' => 'Área de Tecnología e Informática — pensamiento computacional y manejo de herramientas digitales'],
            ['name' => 'Ética y Valores Humanos',      'description' => 'Área de Ética — formación en valores, convivencia y ciudadanía'],
            ['name' => 'Educación Religiosa',          'description' => 'Área de Educación Religiosa — diversidad cultural y espiritual'],
        ];

        foreach ($subjects as $s) {
            DB::table('subject')->insertOrIgnore(array_merge($s, ['created_at' => $now, 'updated_at' => $now]));
        }

        $this->command->info('✓ ' . count($grades) . ' grados y ' . count($subjects) . ' materias creados.');
    }
}

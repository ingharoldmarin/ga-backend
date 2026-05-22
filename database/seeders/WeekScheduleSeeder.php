<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeekScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $teacherRoleId = DB::table('roles')->where('name', 'profesor')->value('id');

        // Tomar profesores con mallas asignadas
        $assignments = DB::table('curriculum_grid_user as cgu')
            ->join('curriculum_grid as cg', 'cg.id', '=', 'cgu.curriculum_grid_id')
            ->join('grade as g',            'g.id',  '=', 'cg.grade_id')
            ->join('subject as s',          's.id',  '=', 'cg.subject_id')
            ->join('roles_user as ru',      'ru.user_id', '=', 'cgu.user_id')
            ->where('ru.role_id', $teacherRoleId)
            ->select('cgu.user_id as teacher_id', 'cg.grade_id', 'cg.subject_id', 'cg.period')
            ->groupBy('cgu.user_id', 'cg.grade_id', 'cg.subject_id', 'cg.period')
            ->get();

        // Obtener nodos del árbol por malla para asignar semanas
        $schedCount = 0;
        $obsCount   = 0;

        foreach ($assignments as $asgn) {
            $grids = DB::table('curriculum_grid')
                ->where('grade_id',   $asgn->grade_id)
                ->where('subject_id', $asgn->subject_id)
                ->where('period',     $asgn->period)
                ->whereNull('deleted_at')
                ->pluck('id');

            // Recopilar nodos únicos de estas mallas
            $topicIds = DB::table('curriculum_grid_topic')
                ->whereIn('curriculum_grid_id', $grids)->distinct()->pluck('topic_id');
            $compIds  = DB::table('curriculum_grid_component')
                ->whereIn('curriculum_grid_id', $grids)->distinct()->pluck('component_id');
            $stdIds   = DB::table('curriculum_grid_standard')
                ->whereIn('curriculum_grid_id', $grids)->distinct()->pluck('standard_id');

            // Asignar semanas (plan de 10 semanas por período, 2-3 semanas por tema)
            $weekOffset = ($asgn->period - 1) * 10; // Periodo 1: 1-10, P2: 11-20, etc.

            $weekCursor = $weekOffset + 1;
            foreach ($topicIds->take(3) as $topicId) {
                $weeks = range($weekCursor, min($weekCursor + 2, $weekOffset + 10));
                DB::table('teacher_week_schedule')->insertOrIgnore([
                    'teacher_id' => $asgn->teacher_id,
                    'grade_id'   => $asgn->grade_id,
                    'subject_id' => $asgn->subject_id,
                    'period'     => $asgn->period,
                    'node_type'  => 'topic',
                    'node_id'    => (string) $topicId,
                    'weeks'      => json_encode(array_values($weeks)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $weekCursor += 3;
                $schedCount++;
            }
            foreach ($compIds->take(2) as $compId) {
                $weeks = range($weekOffset + 1, min($weekOffset + 4, $weekOffset + 10));
                DB::table('teacher_week_schedule')->insertOrIgnore([
                    'teacher_id' => $asgn->teacher_id,
                    'grade_id'   => $asgn->grade_id,
                    'subject_id' => $asgn->subject_id,
                    'period'     => $asgn->period,
                    'node_type'  => 'component',
                    'node_id'    => (string) $compId,
                    'weeks'      => json_encode(array_values($weeks)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $schedCount++;
            }

            // ── Observaciones de seguimiento de ejemplo ──────────────────
            $startWeek = $weekOffset + 1;
            $scenarios = [
                // semana ejecutada
                ['week' => $startWeek,     'executed' => 1, 'obs' => null, 'rescheduled' => null],
                // semana ejecutada
                ['week' => $startWeek + 1, 'executed' => 1, 'obs' => null, 'rescheduled' => null],
                // semana NO ejecutada por festivo
                ['week' => $startWeek + 2, 'executed' => 0, 'obs' => 'Semana no ejecutada por festivo del 7 de agosto. Se reprograma para la siguiente semana.', 'rescheduled' => $startWeek + 3],
                // semana ejecutada (la reprogramada)
                ['week' => $startWeek + 3, 'executed' => 1, 'obs' => null, 'rescheduled' => null],
                // semana NO ejecutada por paro
                ['week' => $startWeek + 4, 'executed' => 0, 'obs' => 'Paro cívico impidió el desarrollo de la clase programada. Pendiente de recuperar.', 'rescheduled' => $startWeek + 6],
                // semana ejecutada
                ['week' => $startWeek + 5, 'executed' => 1, 'obs' => null, 'rescheduled' => null],
                // semana ejecutada (recuperación)
                ['week' => $startWeek + 6, 'executed' => 1, 'obs' => 'Clase de recuperación de la semana ' . ($startWeek + 4), 'rescheduled' => null],
            ];

            if ($topicIds->isNotEmpty()) {
                $topicId = $topicIds->first();
                foreach ($scenarios as $s) {
                    if ($s['week'] > $weekOffset + 10) continue;
                    DB::table('teacher_schedule_observations')->insertOrIgnore([
                        'teacher_id'       => $asgn->teacher_id,
                        'grade_id'         => $asgn->grade_id,
                        'subject_id'       => $asgn->subject_id,
                        'period'           => $asgn->period,
                        'node_type'        => 'topic',
                        'node_id'          => (string) $topicId,
                        'week'             => $s['week'],
                        'executed'         => $s['executed'],
                        'observation'      => $s['obs'],
                        'rescheduled_week' => $s['rescheduled'],
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                    $obsCount++;
                }
            }
        }

        $this->command->info("✓ {$schedCount} entradas de cronograma semanal creadas.");
        $this->command->info("✓ {$obsCount} observaciones de seguimiento creadas.");
    }
}

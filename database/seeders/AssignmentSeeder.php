<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $teacherRoleId = DB::table('roles')->where('name', 'profesor')->value('id');
        $coordRoleId   = DB::table('roles')->where('name', 'coordinador')->value('id');

        // Coordinadores de cada colegio
        $coord1 = DB::table('users')->where('email', 'coordinador@gestion.edu.co')->value('id');
        $coord2 = DB::table('users')->where('email', 'ltorres@gestion.edu.co')->value('id');
        $coord3 = DB::table('users')->where('email', 'hbermudez@gestion.edu.co')->value('id');

        $school1 = DB::table('schools')->where('nit', '890901234-5')->value('id');
        $school2 = DB::table('schools')->where('nit', '890901567-8')->value('id');
        $school3 = DB::table('schools')->where('nit', '890901890-1')->value('id');

        // Profesores por colegio
        $teachers1 = DB::table('users as u')
            ->join('roles_user as ru', 'ru.user_id', '=', 'u.id')
            ->join('user_school as us', 'us.user_id', '=', 'u.id')
            ->where('ru.role_id', $teacherRoleId)
            ->where('us.school_id', $school1)
            ->pluck('u.id')->toArray();

        $teachers2 = DB::table('users as u')
            ->join('roles_user as ru', 'ru.user_id', '=', 'u.id')
            ->join('user_school as us', 'us.user_id', '=', 'u.id')
            ->where('ru.role_id', $teacherRoleId)
            ->where('us.school_id', $school2)
            ->pluck('u.id')->toArray();

        $teachers3 = DB::table('users as u')
            ->join('roles_user as ru', 'ru.user_id', '=', 'u.id')
            ->join('user_school as us', 'us.user_id', '=', 'u.id')
            ->where('ru.role_id', $teacherRoleId)
            ->where('us.school_id', $school3)
            ->pluck('u.id')->toArray();

        // Mallas por materia y grado
        $gridMatemG1P1 = $this->getGridId('Primero',   'Matemáticas',       1);
        $gridMatemG1P2 = $this->getGridId('Primero',   'Matemáticas',       2);
        $gridMatemG1P3 = $this->getGridId('Primero',   'Matemáticas',       3);
        $gridMatemG3P1 = $this->getGridId('Tercero',   'Matemáticas',       1);
        $gridLengG2P1  = $this->getGridId('Segundo',   'Lengua Castellana', 1);
        $gridLengG2P2  = $this->getGridId('Segundo',   'Lengua Castellana', 2);
        $gridLengG6P1  = $this->getGridId('Sexto',     'Lengua Castellana', 1);
        $gridCiNatG5P1 = $this->getGridId('Quinto',    'Ciencias Naturales',1);
        $gridCiSocG9P1 = $this->getGridId('Noveno',    'Ciencias Sociales', 1);
        $gridIngG10P1  = $this->getGridId('Décimo',    'Inglés',            1);

        $gradeIds   = DB::table('grade')->pluck('id', 'name');
        $subjectIds = DB::table('subject')->pluck('id', 'name');

        // ── Asignaciones: mallas a profesores ────────────────────────────

        // Colegio 1 - Profesores asignados a mallas
        $assignGrid = function(int $teacherId, ?int $gridId, ?int $schoolId, ?int $assignedBy) use ($now) {
            if (!$gridId || !$teacherId) return;
            DB::table('curriculum_grid_user')->insertOrIgnore([
                'user_id'             => $teacherId,
                'curriculum_grid_id'  => $gridId,
                'school_id'           => $schoolId,
                'assigned_by'         => $assignedBy,
                'assigned_at'         => $now,
            ]);
        };

        if (count($teachers1) >= 1) {
            $assignGrid($teachers1[0], $gridMatemG1P1, $school1, $coord1);
            $assignGrid($teachers1[0], $gridMatemG1P2, $school1, $coord1);
            $assignGrid($teachers1[0], $gridMatemG1P3, $school1, $coord1);
        }
        if (count($teachers1) >= 2) {
            $assignGrid($teachers1[1], $gridMatemG3P1, $school1, $coord1);
            $assignGrid($teachers1[1], $gridLengG6P1,  $school1, $coord1);
        }
        if (count($teachers1) >= 3) {
            $assignGrid($teachers1[2], $gridLengG2P1,  $school1, $coord1);
            $assignGrid($teachers1[2], $gridLengG2P2,  $school1, $coord1);
        }
        if (count($teachers1) >= 4) {
            $assignGrid($teachers1[3], $gridCiNatG5P1, $school1, $coord1);
        }

        // Colegio 2
        if (count($teachers2) >= 1) {
            $assignGrid($teachers2[0], $gridMatemG1P1, $school2, $coord2);
            $assignGrid($teachers2[0], $gridMatemG1P2, $school2, $coord2);
        }
        if (count($teachers2) >= 2) {
            $assignGrid($teachers2[1], $gridCiNatG5P1, $school2, $coord2);
            $assignGrid($teachers2[1], $gridCiSocG9P1, $school2, $coord2);
        }
        if (count($teachers2) >= 3) {
            $assignGrid($teachers2[2], $gridLengG2P1,  $school2, $coord2);
        }
        if (count($teachers2) >= 4) {
            $assignGrid($teachers2[3], $gridIngG10P1,  $school2, $coord2);
        }

        // Colegio 3
        if (count($teachers3) >= 1) {
            $assignGrid($teachers3[0], $gridMatemG3P1, $school3, $coord3);
            $assignGrid($teachers3[0], $gridCiSocG9P1, $school3, $coord3);
        }
        if (count($teachers3) >= 2) {
            $assignGrid($teachers3[1], $gridIngG10P1,  $school3, $coord3);
            $assignGrid($teachers3[1], $gridLengG6P1,  $school3, $coord3);
        }

        // ── teacher_subject: asignaciones de materia-grado ───────────────
        $assignSubject = function(int $tid, string $gradeName, string $subjectName, int $schoolId, ?int $coordId) use ($gradeIds, $subjectIds, $now) {
            $gId = $gradeIds[$gradeName] ?? null;
            $sId = $subjectIds[$subjectName] ?? null;
            if (!$gId || !$sId) return;
            DB::table('teacher_subject')->insertOrIgnore([
                'teacher_id'  => $tid,
                'grade_id'    => $gId,
                'subject_id'  => $sId,
                'school_id'   => $schoolId,
                'assigned_by' => $coordId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        };

        if (count($teachers1) >= 1) {
            $assignSubject($teachers1[0], 'Primero',  'Matemáticas',       $school1, $coord1);
        }
        if (count($teachers1) >= 2) {
            $assignSubject($teachers1[1], 'Tercero',  'Matemáticas',       $school1, $coord1);
            $assignSubject($teachers1[1], 'Sexto',    'Lengua Castellana', $school1, $coord1);
        }
        if (count($teachers1) >= 3) {
            $assignSubject($teachers1[2], 'Segundo',  'Lengua Castellana', $school1, $coord1);
        }
        if (count($teachers1) >= 4) {
            $assignSubject($teachers1[3], 'Quinto',   'Ciencias Naturales',$school1, $coord1);
        }

        if (count($teachers2) >= 1) {
            $assignSubject($teachers2[0], 'Primero',  'Matemáticas',       $school2, $coord2);
        }
        if (count($teachers2) >= 2) {
            $assignSubject($teachers2[1], 'Quinto',   'Ciencias Naturales',$school2, $coord2);
            $assignSubject($teachers2[1], 'Noveno',   'Ciencias Sociales', $school2, $coord2);
        }
        if (count($teachers2) >= 4) {
            $assignSubject($teachers2[3], 'Décimo',   'Inglés',            $school2, $coord2);
        }

        if (count($teachers3) >= 1) {
            $assignSubject($teachers3[0], 'Tercero',  'Matemáticas',       $school3, $coord3);
        }
        if (count($teachers3) >= 2) {
            $assignSubject($teachers3[1], 'Décimo',   'Inglés',            $school3, $coord3);
        }

        $this->command->info('✓ Mallas asignadas a profesores por colegio.');
        $this->command->info('✓ Asignaciones de materia-grado (teacher_subject) creadas.');
    }

    private function getGridId(string $gradeName, string $subjectName, int $period): ?int
    {
        return DB::table('curriculum_grid as cg')
            ->join('grade as g',   'g.id',  '=', 'cg.grade_id')
            ->join('subject as s', 's.id',  '=', 'cg.subject_id')
            ->where('g.name',  $gradeName)
            ->where('s.name',  $subjectName)
            ->where('cg.period', $period)
            ->whereNull('cg.deleted_at')
            ->value('cg.id');
    }
}

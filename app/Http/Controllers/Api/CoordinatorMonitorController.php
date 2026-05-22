<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurriculumGrid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CoordinatorMonitorController extends Controller
{
    private const CATEGORY_TABLE = [
        'topic'       => 'topic',
        'component'   => 'component',
        'standard'    => 'standard',
        'competence'  => 'competence',
        'affirmation' => 'affirmation_dna_dba',
        'evidence'    => 'evidence_dna_dba',
    ];

    private function requireCoordinator(Request $request)
    {
        $user = $request->user();
        if (!$user->isCoordinator() && !$user->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'Solo coordinadores o administradores pueden acceder');
        }
        return $user;
    }

    /**
     * GET /coordinator/monitor/teacher/{userId}/malla
     * Full personalized malla for a teacher: base assignments + their extras,
     * grouped by grade+subject+period (same structure the teacher sees).
     */
    public function getTeacherMalla(Request $request, int $userId)
    {
        $this->requireCoordinator($request);
        User::findOrFail($userId);

        // Base assignments
        $rows = DB::table('curriculum_grid_user as cgu')
            ->join('curriculum_grid as cg', 'cgu.curriculum_grid_id', '=', 'cg.id')
            ->join('grade as g', 'cg.grade_id', '=', 'g.id')
            ->join('subject as s', 'cg.subject_id', '=', 's.id')
            ->leftJoin('schools as sch', 'cgu.school_id', '=', 'sch.id')
            ->where('cgu.user_id', $userId)
            ->select([
                'cg.id as curriculum_grid_id',
                'cg.period',
                'g.id as grade_id', 'g.name as grade_name',
                's.id as subject_id', 's.name as subject_name',
                'cgu.school_id', 'sch.name as school_name',
            ])
            ->get();

        // Enrich each row with full curriculum data
        $enriched = $rows->map(function ($row) {
            $grid = CurriculumGrid::with([
                'topics', 'components', 'standards', 'competences', 'affirmations', 'evidences'
            ])->find($row->curriculum_grid_id);

            if (!$grid) return null;

            return [
                'curriculum_grid_id' => $row->curriculum_grid_id,
                'grade_id'           => $row->grade_id,
                'grade_name'         => $row->grade_name,
                'subject_id'         => $row->subject_id,
                'subject_name'       => $row->subject_name,
                'period'             => $row->period,
                'school_id'          => $row->school_id,
                'school_name'        => $row->school_name,
                'topics'       => $grid->topics->map(fn($t)  => ['id' => $t->id, 'name' => $t->name,        'description' => $t->description ?? null]),
                'components'   => $grid->components->map(fn($c) => ['id' => $c->id, 'name' => $c->name,     'description' => $c->description ?? null]),
                'standards'    => $grid->standards->map(fn($s)  => ['id' => $s->id, 'name' => $s->name,     'description' => $s->description ?? null]),
                'competences'  => $grid->competences->map(fn($k) => ['id' => $k->id, 'name' => $k->name ?? null, 'description' => $k->description]),
                'affirmations' => $grid->affirmations->map(fn($a) => ['id' => $a->id, 'name' => $a->name,   'description' => $a->description ?? null]),
                'evidences'    => $grid->evidences->map(fn($e)   => ['id' => $e->id, 'name' => $e->name,    'description' => $e->description ?? null]),
            ];
        })->filter()->values();

        // Teacher's extras
        $extras = DB::table('teacher_malla_extras')
            ->where('teacher_id', $userId)
            ->get()
            ->map(function ($row) {
                $table = self::CATEGORY_TABLE[$row->category] ?? null;
                $item  = $table ? DB::table($table)->where('id', $row->item_id)->first() : null;
                return [
                    'grade_id'         => $row->grade_id,
                    'subject_id'       => $row->subject_id,
                    'period'           => $row->period,
                    'category'         => $row->category,
                    'item_id'          => $row->item_id,
                    'item_name'        => $item ? ($item->name ?? $item->description ?? '') : '',
                    'item_description' => $item ? ($item->description ?? null) : null,
                ];
            });

        return response()->json([
            'assignments' => $enriched,
            'extras'      => $extras,
        ]);
    }

    /**
     * GET /coordinator/monitor/teacher/{userId}/schedule
     * Week schedule planned by the teacher.
     */
    public function getTeacherSchedule(Request $request, int $userId)
    {
        $this->requireCoordinator($request);
        User::findOrFail($userId);

        $query = DB::table('teacher_week_schedule')->where('teacher_id', $userId);

        if ($request->filled('grade_id'))   $query->where('grade_id',   $request->query('grade_id'));
        if ($request->filled('subject_id')) $query->where('subject_id', $request->query('subject_id'));
        if ($request->has('period')) {
            $p = $request->query('period');
            $p === '' || $p === null ? $query->whereNull('period') : $query->where('period', $p);
        }

        return response()->json($query->get()->map(fn ($r) => [
            'grade_id'   => $r->grade_id,
            'subject_id' => $r->subject_id,
            'period'     => $r->period,
            'node_type'  => $r->node_type,
            'node_id'    => $r->node_id,
            'weeks'      => json_decode($r->weeks, true),
        ]));
    }

    /**
     * GET /coordinator/monitor/observations?school_id=
     * All schedule observations from every teacher in the coordinator's school(s).
     */
    public function getSchoolObservations(Request $request)
    {
        $coordinator = $this->requireCoordinator($request);

        $schoolId = $request->query('school_id');

        // Determine teacher IDs (role 'profesor') in the coordinator's school(s)
        $teacherRoleId = DB::table('roles')->where('name', 'profesor')->value('id');

        $baseQ = DB::table('user_school as us')
            ->join('roles_user as ru', 'ru.user_id', '=', 'us.user_id')
            ->where('ru.role_id', $teacherRoleId);

        if ($schoolId) {
            $teacherIds = $baseQ->where('us.school_id', $schoolId)->pluck('us.user_id')->unique();
        } else {
            $coordSchools = DB::table('user_school')->where('user_id', $coordinator->id)->pluck('school_id');
            if ($coordSchools->isEmpty()) return response()->json([]);
            $teacherIds = $baseQ->whereIn('us.school_id', $coordSchools)->pluck('us.user_id')->unique();
        }

        if ($teacherIds->isEmpty()) return response()->json([]);

        $query = DB::table('teacher_schedule_observations as obs')
            ->join('users as u', 'obs.teacher_id', '=', 'u.id')
            ->join('grade as g', 'obs.grade_id', '=', 'g.id')
            ->join('subject as s', 'obs.subject_id', '=', 's.id')
            ->whereIn('obs.teacher_id', $teacherIds);

        // Optional extra filters
        if ($request->filled('teacher_id')) $query->where('obs.teacher_id', $request->query('teacher_id'));
        if ($request->filled('grade_id'))   $query->where('obs.grade_id',   $request->query('grade_id'));
        if ($request->filled('subject_id')) $query->where('obs.subject_id', $request->query('subject_id'));
        if ($request->filled('period')) {
            $p = $request->query('period');
            $p === '' || $p === null ? $query->whereNull('obs.period') : $query->where('obs.period', $p);
        }
        if ($request->filled('executed')) {
            $query->where('obs.executed', filter_var($request->query('executed'), FILTER_VALIDATE_BOOLEAN));
        }

        $rows = $query->select([
            'obs.id',
            'obs.teacher_id',
            DB::raw("CONCAT(u.first_name, ' ', u.last_name) as teacher_name"),
            'g.id as grade_id',  'g.name as grade_name',
            's.id as subject_id', 's.name as subject_name',
            'obs.period',
            'obs.node_type',
            'obs.node_id',
            'obs.week',
            'obs.executed',
            'obs.observation',
            'obs.rescheduled_week',
        ])->orderBy('teacher_name')->orderBy('obs.grade_id')->orderBy('obs.week')->get();

        return response()->json($rows->map(fn ($r) => [
            'id'               => $r->id,
            'teacher_id'       => $r->teacher_id,
            'teacher_name'     => $r->teacher_name,
            'grade_id'         => $r->grade_id,
            'grade_name'       => $r->grade_name,
            'subject_id'       => $r->subject_id,
            'subject_name'     => $r->subject_name,
            'period'           => $r->period,
            'node_type'        => $r->node_type,
            'node_id'          => $r->node_id,
            'week'             => $r->week,
            'executed'         => (bool) $r->executed,
            'observation'      => $r->observation,
            'rescheduled_week' => $r->rescheduled_week,
        ]));
    }

    /**
     * GET /coordinator/monitor/statistics?school_id=
     * Aggregated statistics for all teachers in the school.
     * Returns per-teacher, per-malla and school-level metrics.
     */
    public function getStatistics(Request $request)
    {
        $coordinator = $this->requireCoordinator($request);
        $schoolId    = $request->query('school_id');

        // Resolve teacher IDs (role 'profesor') in the school
        $teacherRoleId = DB::table('roles')->where('name', 'profesor')->value('id');

        $baseQuery = DB::table('user_school as us')
            ->join('roles_user as ru', 'ru.user_id', '=', 'us.user_id')
            ->where('ru.role_id', $teacherRoleId);

        if ($schoolId) {
            $teacherIds = $baseQuery->where('us.school_id', $schoolId)->pluck('us.user_id')->unique();
        } else {
            $coordSchools = DB::table('user_school')->where('user_id', $coordinator->id)->pluck('school_id');
            if ($coordSchools->isEmpty()) return response()->json($this->emptyStats());
            $teacherIds = $baseQuery->whereIn('us.school_id', $coordSchools)->pluck('us.user_id')->unique();
        }

        if ($teacherIds->isEmpty()) return response()->json($this->emptyStats());

        // ── 1. Per-teacher planned weeks ──────────────────────────────────
        $plannedRaw = DB::table('teacher_week_schedule')
            ->whereIn('teacher_id', $teacherIds)
            ->select('teacher_id', DB::raw('SUM(JSON_LENGTH(weeks)) as planned'))
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        // ── 2. Per-teacher observation summary ────────────────────────────
        $obsRaw = DB::table('teacher_schedule_observations')
            ->whereIn('teacher_id', $teacherIds)
            ->select(
                'teacher_id',
                DB::raw('COUNT(*) as total_obs'),
                DB::raw('SUM(executed) as executed'),
                DB::raw('SUM(CASE WHEN executed = 0 THEN 1 ELSE 0 END) as not_executed'),
                DB::raw('SUM(CASE WHEN executed = 0 AND observation IS NOT NULL AND observation != \'\' THEN 1 ELSE 0 END) as with_obs'),
                DB::raw('SUM(CASE WHEN rescheduled_week IS NOT NULL THEN 1 ELSE 0 END) as rescheduled')
            )
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        // ── 3. Per-malla planned + obs ────────────────────────────────────
        $mallaPlanned = DB::table('teacher_week_schedule')
            ->whereIn('teacher_id', $teacherIds)
            ->select('teacher_id', 'grade_id', 'subject_id', 'period',
                DB::raw('SUM(JSON_LENGTH(weeks)) as planned'))
            ->groupBy('teacher_id', 'grade_id', 'subject_id', 'period')
            ->get();

        $mallaObs = DB::table('teacher_schedule_observations')
            ->whereIn('teacher_id', $teacherIds)
            ->select('teacher_id', 'grade_id', 'subject_id', 'period',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(executed) as executed'),
                DB::raw('SUM(CASE WHEN executed = 0 THEN 1 ELSE 0 END) as not_executed'))
            ->groupBy('teacher_id', 'grade_id', 'subject_id', 'period')
            ->get();

        // ── 4. Teacher names ──────────────────────────────────────────────
        $teacherNames = DB::table('users')
            ->whereIn('id', $teacherIds)
            ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) as name"))
            ->pluck('name', 'id');

        // ── 5. Grade & Subject names ──────────────────────────────────────
        $gradeNames   = DB::table('grade')->pluck('name', 'id');
        $subjectNames = DB::table('subject')->pluck('name', 'id');

        // ── Build per-teacher summary ─────────────────────────────────────
        $perTeacher = $teacherIds->map(function ($tid) use ($plannedRaw, $obsRaw, $teacherNames) {
            $planned  = (int) ($plannedRaw[$tid]->planned  ?? 0);
            $obs      = $obsRaw[$tid] ?? null;
            $executed = $obs ? (int) $obs->executed      : 0;
            $notExec  = $obs ? (int) $obs->not_executed  : 0;
            $withObs  = $obs ? (int) $obs->with_obs      : 0;
            $resched  = $obs ? (int) $obs->rescheduled   : 0;
            $tracked  = $obs ? (int) $obs->total_obs     : 0;
            $rate     = $planned > 0 ? round($executed / $planned * 100, 1) : 0;

            return [
                'teacher_id'   => $tid,
                'teacher_name' => $teacherNames[$tid] ?? "Docente #$tid",
                'planned'      => $planned,
                'tracked'      => $tracked,
                'executed'     => $executed,
                'not_executed' => $notExec,
                'with_obs'     => $withObs,
                'rescheduled'  => $resched,
                'exec_rate'    => $rate,
                'pending'      => max(0, $planned - $executed),
            ];
        })->values();

        // ── Build per-malla breakdown ─────────────────────────────────────
        $mallaObsMap = [];
        foreach ($mallaObs as $row) {
            $k = "{$row->teacher_id}-{$row->grade_id}-{$row->subject_id}-{$row->period}";
            $mallaObsMap[$k] = $row;
        }

        $perMalla = $mallaPlanned->map(function ($row) use ($mallaObsMap, $teacherNames, $gradeNames, $subjectNames) {
            $k   = "{$row->teacher_id}-{$row->grade_id}-{$row->subject_id}-{$row->period}";
            $obs = $mallaObsMap[$k] ?? null;
            $planned  = (int) $row->planned;
            $executed = $obs ? (int) $obs->executed    : 0;
            $notExec  = $obs ? (int) $obs->not_executed : 0;
            $rate     = $planned > 0 ? round($executed / $planned * 100, 1) : 0;
            return [
                'teacher_id'   => $row->teacher_id,
                'teacher_name' => $teacherNames[$row->teacher_id] ?? "Docente #{$row->teacher_id}",
                'grade_id'     => $row->grade_id,
                'grade_name'   => $gradeNames[$row->grade_id]     ?? "Grado #{$row->grade_id}",
                'subject_id'   => $row->subject_id,
                'subject_name' => $subjectNames[$row->subject_id] ?? "Materia #{$row->subject_id}",
                'period'       => $row->period,
                'planned'      => $planned,
                'executed'     => $executed,
                'not_executed' => $notExec,
                'exec_rate'    => $rate,
            ];
        })->values();

        // ── School totals ─────────────────────────────────────────────────
        $totals = [
            'teachers'     => $teacherIds->count(),
            'planned'      => $perTeacher->sum('planned'),
            'executed'     => $perTeacher->sum('executed'),
            'not_executed' => $perTeacher->sum('not_executed'),
            'rescheduled'  => $perTeacher->sum('rescheduled'),
            'with_obs'     => $perTeacher->sum('with_obs'),
            'exec_rate'    => $perTeacher->sum('planned') > 0
                ? round($perTeacher->sum('executed') / $perTeacher->sum('planned') * 100, 1)
                : 0,
        ];

        return response()->json([
            'totals'      => $totals,
            'per_teacher' => $perTeacher,
            'per_malla'   => $perMalla,
        ]);
    }

    private function emptyStats(): array
    {
        return [
            'totals'      => ['teachers' => 0, 'planned' => 0, 'executed' => 0, 'not_executed' => 0, 'rescheduled' => 0, 'with_obs' => 0, 'exec_rate' => 0],
            'per_teacher' => [],
            'per_malla'   => [],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherScheduleObservationController extends Controller
{
    /**
     * GET /teacher/schedule-observations
     * Returns all observations for the authenticated teacher,
     * filtered by grade, subject and period.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $query = DB::table('teacher_schedule_observations')->where('teacher_id', $teacher->id);

        if ($request->filled('grade_id'))   $query->where('grade_id',   $request->query('grade_id'));
        if ($request->filled('subject_id')) $query->where('subject_id', $request->query('subject_id'));

        if ($request->has('period')) {
            $p = $request->query('period');
            $p === '' || $p === null
                ? $query->whereNull('period')
                : $query->where('period', $p);
        }

        return response()->json($query->get()->map(fn ($r) => [
            'id'               => $r->id,
            'grade_id'         => $r->grade_id,
            'subject_id'       => $r->subject_id,
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
     * POST /teacher/schedule-observations/batch
     * Replace ALL observations for a given grade+subject+period.
     *
     * Body: {
     *   grade_id, subject_id, period?,
     *   items: [{ node_type, node_id, week, executed, observation?, rescheduled_week? }, ...]
     * }
     */
    public function batchSave(Request $request)
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'grade_id'                  => ['required', 'integer', 'exists:grade,id'],
            'subject_id'                => ['required', 'integer', 'exists:subject,id'],
            'period'                    => ['nullable', 'integer', 'min:1', 'max:4'],
            'items'                     => ['required', 'array'],
            'items.*.node_type'         => ['required', 'string', 'in:topic,component,standard,competence,affirmation,evidence'],
            'items.*.node_id'           => ['required', 'string', 'max:50'],
            'items.*.week'              => ['required', 'integer', 'min:1', 'max:52'],
            'items.*.executed'          => ['required', 'boolean'],
            'items.*.observation'       => ['nullable', 'string', 'max:1000'],
            'items.*.rescheduled_week'  => ['nullable', 'integer', 'min:1', 'max:52'],
        ]);

        $period = $validated['period'] ?? null;

        DB::beginTransaction();
        try {
            // Replace all observations for this malla
            DB::table('teacher_schedule_observations')
                ->where('teacher_id', $teacher->id)
                ->where('grade_id',   $validated['grade_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where(function ($q) use ($period) {
                    $period !== null
                        ? $q->where('period', $period)
                        : $q->whereNull('period');
                })
                ->delete();

            $now  = now();
            $rows = [];

            foreach ($validated['items'] as $item) {
                $rows[] = [
                    'teacher_id'       => $teacher->id,
                    'grade_id'         => $validated['grade_id'],
                    'subject_id'       => $validated['subject_id'],
                    'period'           => $period,
                    'node_type'        => $item['node_type'],
                    'node_id'          => (string) $item['node_id'],
                    'week'             => $item['week'],
                    'executed'         => $item['executed'] ? 1 : 0,
                    'observation'      => $item['observation'] ?? null,
                    'rescheduled_week' => $item['rescheduled_week'] ?? null,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            if ($rows) DB::table('teacher_schedule_observations')->insert($rows);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(['saved' => count($rows)]);
    }
}

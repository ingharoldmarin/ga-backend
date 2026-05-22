<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherWeekScheduleController extends Controller
{
    /**
     * GET /teacher/week-schedule
     * Returns all week schedule assignments for the authenticated teacher,
     * optionally filtered by grade, subject and period.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $query = DB::table('teacher_week_schedule')->where('teacher_id', $teacher->id);

        if ($request->filled('grade_id'))   $query->where('grade_id',   $request->query('grade_id'));
        if ($request->filled('subject_id')) $query->where('subject_id', $request->query('subject_id'));

        if ($request->has('period')) {
            $p = $request->query('period');
            $p === '' || $p === null
                ? $query->whereNull('period')
                : $query->where('period', $p);
        }

        $rows = $query->get()->map(fn ($r) => [
            'id'         => $r->id,
            'grade_id'   => $r->grade_id,
            'subject_id' => $r->subject_id,
            'period'     => $r->period,
            'node_type'  => $r->node_type,
            'node_id'    => $r->node_id,
            'weeks'      => json_decode($r->weeks, true),
        ]);

        return response()->json($rows);
    }

    /**
     * POST /teacher/week-schedule/batch
     * Replace ALL schedule entries for a given grade+subject+period
     * with the provided nodes list.
     *
     * Body: {
     *   grade_id, subject_id, period?,
     *   nodes: [{ node_type, node_id, weeks: [1,2,3,...] }, ...]
     * }
     */
    public function batchSave(Request $request)
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'grade_id'            => ['required', 'integer', 'exists:grade,id'],
            'subject_id'          => ['required', 'integer', 'exists:subject,id'],
            'period'              => ['nullable', 'integer', 'min:1', 'max:4'],
            'nodes'               => ['required', 'array'],
            'nodes.*.node_type'   => ['required', 'string', 'in:topic,component,standard,competence,affirmation,evidence'],
            'nodes.*.node_id'     => ['required', 'string', 'max:50'],
            'nodes.*.weeks'       => ['present', 'array'],
            'nodes.*.weeks.*'     => ['integer', 'min:1', 'max:52'],
        ]);

        $period = $validated['period'] ?? null;

        DB::beginTransaction();
        try {
            // Delete previous schedule for this malla
            DB::table('teacher_week_schedule')
                ->where('teacher_id', $teacher->id)
                ->where('grade_id',   $validated['grade_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where(function ($q) use ($period) {
                    $period !== null
                        ? $q->where('period', $period)
                        : $q->whereNull('period');
                })
                ->delete();

            // Insert new rows (only nodes with at least one week)
            $now  = now();
            $rows = [];

            foreach ($validated['nodes'] as $node) {
                $weeks = array_values(array_unique($node['weeks']));
                if (empty($weeks)) continue;

                $rows[] = [
                    'teacher_id' => $teacher->id,
                    'grade_id'   => $validated['grade_id'],
                    'subject_id' => $validated['subject_id'],
                    'period'     => $period,
                    'node_type'  => $node['node_type'],
                    'node_id'    => (string) $node['node_id'],
                    'weeks'      => json_encode($weeks),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('teacher_week_schedule')->insert($rows);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(['saved' => count($rows ?? [])]);
    }
}

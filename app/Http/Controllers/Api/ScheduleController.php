<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    private string $table = 'schedule';

    public function index(Request $request)
    {
        $query = DB::table($this->table)
            ->select('*');

        // Optional filters
        if ($request->filled('grade_id')) {
            $query->where('grade_id', (int) $request->query('grade_id'));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->query('subject_id'));
        }
        if ($request->filled('week_id')) {
            $query->where('week_id', (int) $request->query('week_id'));
        }
        if ($request->filled('week_ids')) {
            $ids = array_filter(array_map('intval', explode(',', $request->query('week_ids'))));
            if (!empty($ids)) {
                $query->whereIn('week_id', $ids);
            }
        }

        $perPage = (int) min(max((int) $request->query('per_page', 15), 1), 100);
        return $query->paginate($perPage);
    }

    public function show(int $id)
    {
        $row = DB::table($this->table)->where('id', $id)->first();
        if (!$row) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return response()->json($row);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $id = DB::table($this->table)->insertGetId($data);
        $row = DB::table($this->table)->where('id', $id)->first();
        return response()->json($row, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $exists = DB::table($this->table)->where('id', $id)->exists();
        if (!$exists) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $data = $this->validatedData($request, partial: true);
        DB::table($this->table)->where('id', $id)->update($data);
        $row = DB::table($this->table)->where('id', $id)->first();
        return response()->json($row);
    }

    public function destroy(int $id)
    {
        $deleted = DB::table($this->table)->where('id', $id)->delete();
        if ($deleted === 0) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return response()->noContent();
    }

    public function batchStore(Request $request)
    {
        // Common required fields (without requiring week_id here)
        $base = $this->validatedData($request, partial: false, requireWeekId: false);
        $weekIds = $request->input('week_ids');
        if (!is_array($weekIds) || empty($weekIds)) {
            return response()->json([
                'message' => 'week_ids debe ser un arreglo con al menos un id de semana'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $weeks = array_values(array_unique(array_map('intval', $weekIds)));
        if (empty($weeks)) {
            return response()->json([
                'message' => 'week_ids inválidos'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $now = now();
        $rows = [];
        foreach ($weeks as $wid) {
            $rows[] = array_merge($base, [
                'week_id' => $wid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table($this->table)->insert($rows);

        $inserted = DB::table($this->table)
            ->where('grade_id', $base['grade_id'])
            ->where('subject_id', $base['subject_id'])
            ->whereIn('week_id', $weeks)
            ->orderByDesc('id')
            ->limit(count($weeks))
            ->get();

        return response()->json($inserted, Response::HTTP_CREATED);
    }

    private function validatedData(Request $request, bool $partial = false, bool $requireWeekId = true): array
    {
        // Rules for schedule table
        $rules = [
            'grade_id' => $partial ? ['sometimes', 'integer', 'exists:grade,id'] : ['required', 'integer', 'exists:grade,id'],
            'subject_id' => $partial ? ['sometimes', 'integer', 'exists:subject,id'] : ['required', 'integer', 'exists:subject,id'],
            'week_id' => $requireWeekId
                ? ($partial ? ['sometimes', 'integer', 'exists:week,id'] : ['required', 'integer', 'exists:week,id'])
                : ['sometimes', 'integer', 'exists:week,id'],
            'topic_id' => $partial ? ['sometimes', 'integer', 'exists:topic,id'] : ['required', 'integer', 'exists:topic,id'],
            'component_id' => $partial ? ['sometimes', 'integer', 'exists:component,id'] : ['required', 'integer', 'exists:component,id'],
            'didactic_unit_id' => $partial ? ['sometimes', 'integer', 'exists:didactic_unit,id'] : ['required', 'integer', 'exists:didactic_unit,id'],
            'standard_id' => $partial ? ['sometimes', 'integer', 'exists:standard,id'] : ['required', 'integer', 'exists:standard,id'],
            'competence_id' => $partial ? ['sometimes', 'integer', 'exists:competence,id'] : ['required', 'integer', 'exists:competence,id'],
            'affirmation_id' => $partial ? ['sometimes', 'integer', 'exists:affirmation_dna_dba,id'] : ['required', 'integer', 'exists:affirmation_dna_dba,id'],
            'evidence_id' => $partial ? ['sometimes', 'integer', 'exists:evidence_dna_dba,id'] : ['required', 'integer', 'exists:evidence_dna_dba,id'],
            'school_id' => $partial ? ['sometimes', 'integer', 'exists:schools,id'] : ['required', 'integer', 'exists:schools,id'],
            'executed' => $partial ? ['sometimes', 'boolean'] : ['required', 'boolean'],
            'observation' => $partial ? ['sometimes', 'nullable', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($rules);

        // Normalize booleans
        if (array_key_exists('executed', $validated)) {
            $validated['executed'] = (bool) $validated['executed'];
        }

        return $validated;
    }
}

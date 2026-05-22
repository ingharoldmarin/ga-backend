<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherMallaExtrasController extends Controller
{
    private const CATEGORY_TABLE = [
        'topic'       => 'topic',
        'component'   => 'component',
        'standard'    => 'standard',
        'competence'  => 'competence',
        'affirmation' => 'affirmation_dna_dba',
        'evidence'    => 'evidence_dna_dba',
    ];

    /**
     * GET /teacher/malla-extras
     * Returns all extras for the authenticated teacher, optionally filtered by malla.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();
        $query = DB::table('teacher_malla_extras')->where('teacher_id', $teacher->id);

        if ($request->filled('grade_id'))   $query->where('grade_id',   $request->query('grade_id'));
        if ($request->filled('subject_id')) $query->where('subject_id', $request->query('subject_id'));

        if ($request->has('period')) {
            $p = $request->query('period');
            $p === '' || $p === null ? $query->whereNull('period') : $query->where('period', $p);
        }

        $extras = $query->get();

        // Enrich with item data
        $enriched = $extras->map(function ($row) {
            $table = self::CATEGORY_TABLE[$row->category] ?? null;
            $item  = $table ? DB::table($table)->where('id', $row->item_id)->first() : null;
            return [
                'id'               => $row->id,
                'grade_id'         => $row->grade_id,
                'subject_id'       => $row->subject_id,
                'period'           => $row->period,
                'category'         => $row->category,
                'item_id'          => $row->item_id,
                'item_name'        => $item ? ($item->name ?? $item->description ?? '') : '',
                'item_description' => $item ? ($item->description ?? null) : null,
            ];
        });

        return response()->json($enriched);
    }

    /**
     * POST /teacher/malla-extras
     * Add one or more items to a teacher's personal malla.
     * Body: { grade_id, subject_id, period?, category, item_ids: [] }
     */
    public function store(Request $request)
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'grade_id'   => ['required', 'integer', 'exists:grade,id'],
            'subject_id' => ['required', 'integer', 'exists:subject,id'],
            'period'     => ['nullable', 'integer', 'min:1', 'max:4'],
            'category'   => ['required', 'string', 'in:topic,component,standard,competence,affirmation,evidence'],
            'item_ids'   => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
        ]);

        $table = self::CATEGORY_TABLE[$validated['category']];
        $inserted = [];

        foreach ($validated['item_ids'] as $itemId) {
            // Verify item exists
            if (!DB::table($table)->where('id', $itemId)->exists()) continue;

            DB::table('teacher_malla_extras')->insertOrIgnore([
                'teacher_id' => $teacher->id,
                'grade_id'   => $validated['grade_id'],
                'subject_id' => $validated['subject_id'],
                'period'     => $validated['period'] ?? null,
                'category'   => $validated['category'],
                'item_id'    => $itemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $item = DB::table($table)->where('id', $itemId)->first();
            $inserted[] = [
                'item_id'          => $itemId,
                'item_name'        => $item ? ($item->name ?? $item->description ?? '') : '',
                'item_description' => $item ? ($item->description ?? null) : null,
            ];
        }

        return response()->json(['added' => $inserted], 201);
    }

    /**
     * DELETE /teacher/malla-extras/{id}
     * Remove a specific extra from the teacher's personal malla.
     */
    public function destroy(Request $request, int $id)
    {
        $teacher = $request->user();

        $deleted = DB::table('teacher_malla_extras')
            ->where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->noContent();
    }

    /**
     * GET /teacher/malla-catalog
     * Returns all available items for a given category (for the picker).
     * Query: category=topic|component|standard|competence|affirmation|evidence
     */
    public function catalog(Request $request)
    {
        $category = $request->query('category');
        $table    = self::CATEGORY_TABLE[$category] ?? null;

        if (!$table) {
            return response()->json(['message' => 'Categoría inválida'], 422);
        }

        $rows = DB::table($table)->orderBy('id')->get();

        $items = $rows->map(fn($r) => [
            'id'          => $r->id,
            'name'        => $r->name ?? $r->description ?? '',
            'description' => $r->description ?? null,
        ]);

        return response()->json($items);
    }
}

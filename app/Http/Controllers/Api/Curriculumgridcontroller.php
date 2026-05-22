<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurriculumGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CurriculumGridController extends Controller
{
    /**
     * Listar malla curricular con filtros
     */
    public function index(Request $request)
    {
        Log::info('Listando malla curricular', $request->query());

        $query = CurriculumGrid::with([
            'grade',
            'subject',
            'topics',
            'components',
            'standards',
            'competences',
            'affirmations',
            'evidences'
        ]);

        // Filtros
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->query('grade_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->query('subject_id'));
        }

        if ($request->filled('period')) {
            $query->where('period', $request->query('period'));
        }

        if ($request->filled('active')) {
            $active = filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('active', $active);
        }

        $query->orderBy('order', 'asc')->orderBy('id', 'asc');

        $perPage = (int) min(max((int) $request->query('per_page', 25), 1), 500);
        
        $result = $query->paginate($perPage);

        // Transformar datos para incluir nombres
        $result->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'grade_id' => $item->grade_id,
                'subject_id' => $item->subject_id,
                'period' => $item->period,
                'grade_name' => $item->grade->name ?? null,
                'subject_name' => $item->subject->name ?? null,
                'description' => $item->description,
                'order' => $item->order,
                'active' => $item->active,
                'topics' => $item->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'description' => $t->description ?? null]),
                'components' => $item->components->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'description' => $c->description ?? null]),
                'standards' => $item->standards->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'description' => $s->description ?? null]),
                'competences' => $item->competences->map(fn($c) => ['id' => $c->id, 'name' => $c->name ?? null, 'description' => $c->description]),
                'affirmations' => $item->affirmations->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'description' => $a->description ?? null]),
                'evidences' => $item->evidences->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'description' => $e->description ?? null]),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return $result;
    }

    /**
     * Obtener un elemento específico
     */
    public function show(int $id)
    {
        $item = CurriculumGrid::with([
            'grade',
            'subject',
            'topics',
            'components',
            'standards',
            'competences',
            'affirmations',
            'evidences'
        ])->findOrFail($id);

        return response()->json([
            'id' => $item->id,
            'grade_id' => $item->grade_id,
            'subject_id' => $item->subject_id,
            'period' => $item->period,
            'grade_name' => $item->grade->name ?? null,
            'subject_name' => $item->subject->name ?? null,
            'description' => $item->description,
            'order' => $item->order,
            'active' => $item->active,
            'topics' => $item->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'description' => $t->description ?? null]),
            'components' => $item->components->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'description' => $c->description ?? null]),
            'standards' => $item->standards->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'description' => $s->description ?? null]),
            'competences' => $item->competences->map(fn($c) => ['id' => $c->id, 'name' => $c->name ?? null, 'description' => $c->description]),
            'affirmations' => $item->affirmations->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'description' => $a->description ?? null]),
            'evidences' => $item->evidences->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'description' => $e->description ?? null]),
        ]);
    }

    /**
     * Crear nueva entrada con múltiples elementos
     */
    public function store(Request $request)
    {
        Log::info('Creando entrada de malla curricular', $request->all());

        $validated = $request->validate([
            'grade_id' => ['required', 'integer', 'exists:grade,id'],
            'subject_id' => ['required', 'integer', 'exists:subject,id'],
            'period' => ['nullable', 'integer', 'min:1', 'max:4'],
            'topic_ids' => ['required', 'array', 'min:1'],
            'topic_ids.*' => ['integer', 'exists:topic,id'],
            'component_ids' => ['required', 'array', 'min:1'],
            'component_ids.*' => ['integer', 'exists:component,id'],
            'standard_ids' => ['required', 'array', 'min:1'],
            'standard_ids.*' => ['integer', 'exists:standard,id'],
            'competence_ids' => ['required', 'array', 'min:1'],
            'competence_ids.*' => ['integer', 'exists:competence,id'],
            'affirmation_ids' => ['required', 'array', 'min:1'],
            'affirmation_ids.*' => ['integer', 'exists:affirmation_dna_dba,id'],
            'evidence_ids' => ['required', 'array', 'min:1'],
            'evidence_ids.*' => ['integer', 'exists:evidence_dna_dba,id'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        // ── Verificar si ya existe exactamente este camino (misma evidencia en mismo contexto) ──
        $evidenceId = $validated['evidence_ids'][0];
        $existingId = DB::table('curriculum_grid as cg')
            ->join('curriculum_grid_evidence as cge', 'cge.curriculum_grid_id', '=', 'cg.id')
            ->where('cg.grade_id',   $validated['grade_id'])
            ->where('cg.subject_id', $validated['subject_id'])
            ->where(function ($q) use ($validated) {
                $period = $validated['period'] ?? null;
                $period ? $q->where('cg.period', $period) : $q->whereNull('cg.period');
            })
            ->where('cge.evidence_id', $evidenceId)
            ->whereNull('cg.deleted_at')
            ->value('cg.id');

        if ($existingId) {
            $existing = CurriculumGrid::with([
                'topics', 'components', 'standards', 'competences', 'affirmations', 'evidences'
            ])->findOrFail($existingId);
            return response()->json($existing); // idempotente: devuelve el existente
        }

        DB::beginTransaction();

        try {
            // Crear entrada base
            $grid = CurriculumGrid::create([
                'grade_id'   => $validated['grade_id'],
                'subject_id' => $validated['subject_id'],
                'period'     => $validated['period'] ?? null,
                'description' => $validated['description'] ?? null,
                'order'      => $validated['order'] ?? 0,
                'active'     => $validated['active'] ?? true,
            ]);

            // Asociar múltiples relaciones
            $grid->topics()->attach($validated['topic_ids']);
            $grid->components()->attach($validated['component_ids']);
            $grid->standards()->attach($validated['standard_ids']);
            $grid->competences()->attach($validated['competence_ids']);
            $grid->affirmations()->attach($validated['affirmation_ids']);
            $grid->evidences()->attach($validated['evidence_ids']);

            DB::commit();

            Log::info('Malla curricular creada con ID: ' . $grid->id);

            // Cargar relaciones
            $grid->load(['topics', 'components', 'standards', 'competences', 'affirmations', 'evidences']);

            return response()->json($grid, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando malla curricular: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar entrada
     */
    public function update(Request $request, int $id)
    {
        Log::info('Actualizando malla curricular ID: ' . $id, $request->all());

        $grid = CurriculumGrid::findOrFail($id);

        $validated = $request->validate([
            'grade_id' => ['sometimes', 'integer', 'exists:grade,id'],
            'subject_id' => ['sometimes', 'integer', 'exists:subject,id'],
            'topic_ids' => ['sometimes', 'array'],
            'topic_ids.*' => ['integer', 'exists:topic,id'],
            'component_ids' => ['sometimes', 'array'],
            'component_ids.*' => ['integer', 'exists:component,id'],
            'standard_ids' => ['sometimes', 'array'],
            'standard_ids.*' => ['integer', 'exists:standard,id'],
            'competence_ids' => ['sometimes', 'array'],
            'competence_ids.*' => ['integer', 'exists:competence,id'],
            'affirmation_ids' => ['sometimes', 'array'],
            'affirmation_ids.*' => ['integer', 'exists:affirmation_dna_dba,id'],
            'evidence_ids' => ['sometimes', 'array'],
            'evidence_ids.*' => ['integer', 'exists:evidence_dna_dba,id'],
            'description' => ['nullable', 'string'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ]);

        DB::beginTransaction();
        
        try {
            // Actualizar campos base
            $grid->update(array_intersect_key($validated, array_flip([
                'grade_id', 'subject_id', 'description', 'order', 'active'
            ])));

            // Sincronizar relaciones múltiples si se enviaron
            if (isset($validated['topic_ids'])) {
                $grid->topics()->sync($validated['topic_ids']);
            }
            if (isset($validated['component_ids'])) {
                $grid->components()->sync($validated['component_ids']);
            }
            if (isset($validated['standard_ids'])) {
                $grid->standards()->sync($validated['standard_ids']);
            }
            if (isset($validated['competence_ids'])) {
                $grid->competences()->sync($validated['competence_ids']);
            }
            if (isset($validated['affirmation_ids'])) {
                $grid->affirmations()->sync($validated['affirmation_ids']);
            }
            if (isset($validated['evidence_ids'])) {
                $grid->evidences()->sync($validated['evidence_ids']);
            }

            DB::commit();

            Log::info('Malla curricular actualizada');

            $grid->load(['topics', 'components', 'standards', 'competences', 'affirmations', 'evidences']);

            return response()->json($grid);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando malla curricular: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar entrada
     */
    public function destroy(int $id)
    {
        Log::info('Eliminando malla curricular ID: ' . $id);

        $grid = CurriculumGrid::findOrFail($id);
        
        // Las relaciones se eliminan automáticamente por onDelete('cascade')
        $grid->delete();

        Log::info('Malla curricular eliminada');

        return response()->noContent();
    }

    /**
     * Obtener resumen
     */
    public function summary()
    {
        $summary = DB::table('curriculum_grid as cg')
            ->select([
                'g.id as grade_id',
                'g.name as grade_name',
                's.id as subject_id',
                's.name as subject_name',
                DB::raw('COUNT(cg.id) as total_items'),
                DB::raw('SUM(CASE WHEN cg.active = 1 THEN 1 ELSE 0 END) as active_items'),
            ])
            ->leftJoin('grade as g', 'cg.grade_id', '=', 'g.id')
            ->leftJoin('subject as s', 'cg.subject_id', '=', 's.id')
            ->groupBy('g.id', 'g.name', 's.id', 's.name')
            ->orderBy('g.id')
            ->orderBy('s.name')
            ->get();

        return response()->json($summary);
    }
}
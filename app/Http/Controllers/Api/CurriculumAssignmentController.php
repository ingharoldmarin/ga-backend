<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurriculumGrid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CurriculumAssignmentController extends Controller
{
    /**
     * Asignar una malla curricular a uno o múltiples profesores
     * POST /curriculum-grid/{id}/assign
     */
    public function assign(Request $request, int $curriculumGridId)
    {
        Log::info("Asignando malla curricular ID: {$curriculumGridId}");
        
        $validated = $request->validate([
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        $grid = CurriculumGrid::findOrFail($curriculumGridId);
        $coordinator = $request->user();

        // Verificar que el coordinador tenga ese rol
        if (!$coordinator->isCoordinator() && !$coordinator->isAdmin()) {
            return response()->json([
                'message' => 'Solo coordinadores o administradores pueden asignar mallas'
            ], Response::HTTP_FORBIDDEN);
        }

        $assignments = [];
        $errors = [];

        foreach ($validated['teacher_ids'] as $teacherId) {
            try {
                $teacher = User::findOrFail($teacherId);
                
                // Verificar que el usuario sea profesor
                if (!$teacher->isTeacher() && !$teacher->isAdmin()) {
                    $errors[] = [
                        'teacher_id' => $teacherId,
                        'message' => 'El usuario no tiene rol de profesor'
                    ];
                    continue;
                }

                // Asignar la malla (syncWithoutDetaching evita duplicados)
                $teacher->curriculumGrids()->syncWithoutDetaching([
                    $curriculumGridId => [
                        'assigned_by' => $coordinator->id,
                        'school_id' => $validated['school_id'] ?? null,
                        'assigned_at' => now(),
                    ]
                ]);

                $assignments[] = [
                    'teacher_id' => $teacherId,
                    'teacher_name' => "{$teacher->first_name} {$teacher->last_name}",
                    'curriculum_grid_id' => $curriculumGridId,
                ];

                Log::info("Malla asignada a profesor ID: {$teacherId}");
            } catch (\Exception $e) {
                $errors[] = [
                    'teacher_id' => $teacherId,
                    'message' => $e->getMessage()
                ];
                Log::error("Error asignando malla a profesor {$teacherId}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Asignación procesada',
            'success_count' => count($assignments),
            'error_count' => count($errors),
            'assignments' => $assignments,
            'errors' => $errors,
        ], Response::HTTP_CREATED);
    }

    /**
     * Desasignar una malla curricular de un profesor
     * DELETE /curriculum-grid/{id}/unassign/{userId}
     */
    public function unassign(Request $request, int $curriculumGridId, int $userId)
    {
        Log::info("Desasignando malla {$curriculumGridId} del usuario {$userId}");

        $grid = CurriculumGrid::findOrFail($curriculumGridId);
        $teacher = User::findOrFail($userId);
        $coordinator = $request->user();

        // Verificar permisos
        if (!$coordinator->isCoordinator() && !$coordinator->isAdmin()) {
            return response()->json([
                'message' => 'Solo coordinadores o administradores pueden desasignar mallas'
            ], Response::HTTP_FORBIDDEN);
        }

        // Desasignar
        $teacher->curriculumGrids()->detach($curriculumGridId);

        return response()->json([
            'message' => 'Malla desasignada exitosamente',
            'teacher_id' => $userId,
            'curriculum_grid_id' => $curriculumGridId,
        ]);
    }

    /**
     * Obtener todas las mallas asignadas al profesor autenticado
     * GET /curriculum-grid/assigned
     */
    public function getMyAssignments(Request $request)
    {
        $user = $request->user();
        
        Log::info("Obteniendo mallas asignadas para usuario: {$user->id}");

        // Obtener mallas con información de asignación
        $assignments = DB::table('curriculum_grid_user as cgu')
            ->join('curriculum_grid as cg', 'cgu.curriculum_grid_id', '=', 'cg.id')
            ->join('grade as g', 'cg.grade_id', '=', 'g.id')
            ->join('subject as s', 'cg.subject_id', '=', 's.id')
            ->leftJoin('users as coordinator', 'cgu.assigned_by', '=', 'coordinator.id')
            ->leftJoin('schools as sch', 'cgu.school_id', '=', 'sch.id')
            ->where('cgu.user_id', $user->id)
            ->select([
                'cg.id as curriculum_grid_id',
                'cg.description',
                'cg.order',
                'cg.active',
                'g.id as grade_id',
                'g.name as grade_name',
                's.id as subject_id',
                's.name as subject_name',
                'cgu.assigned_at',
                'cgu.school_id',
                'sch.name as school_name',
                DB::raw("CONCAT(coordinator.first_name, ' ', coordinator.last_name) as assigned_by_name"),
            ])
            ->orderBy('cgu.assigned_at', 'desc')
            ->get();

        // Cargar relaciones detalladas para cada malla
        $detailedAssignments = $assignments->map(function ($assignment) {
            $grid = CurriculumGrid::with([
                'topics',
                'components',
                'standards',
                'competences',
                'affirmations',
                'evidences'
            ])->find($assignment->curriculum_grid_id);

            return [
                'curriculum_grid_id' => $assignment->curriculum_grid_id,
                'grade_id' => $assignment->grade_id,
                'grade_name' => $assignment->grade_name,
                'subject_id' => $assignment->subject_id,
                'subject_name' => $assignment->subject_name,
                'description' => $assignment->description,
                'order' => $assignment->order,
                'active' => (bool) $assignment->active,
                'assigned_at' => $assignment->assigned_at,
                'assigned_by_name' => $assignment->assigned_by_name,
                'school_id' => $assignment->school_id,
                'school_name' => $assignment->school_name,
                // Datos completos de la malla
                'topics' => $grid->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
                'components' => $grid->components->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                'standards' => $grid->standards->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                'competences' => $grid->competences->map(fn($c) => ['id' => $c->id, 'description' => $c->description]),
                'affirmations' => $grid->affirmations->map(fn($a) => ['id' => $a->id, 'name' => $a->name]),
                'evidences' => $grid->evidences->map(fn($e) => ['id' => $e->id, 'name' => $e->name]),
            ];
        });

        return response()->json($detailedAssignments);
    }

    /**
     * Obtener todas las mallas de un profesor específico (para coordinadores)
     * GET /users/{userId}/curriculum-grids
     */
    public function getTeacherAssignments(Request $request, int $userId)
    {
        $coordinator = $request->user();
        
        // Verificar permisos
        if (!$coordinator->isCoordinator() && !$coordinator->isAdmin()) {
            return response()->json([
                'message' => 'Solo coordinadores o administradores pueden ver asignaciones de otros usuarios'
            ], Response::HTTP_FORBIDDEN);
        }

        $teacher = User::findOrFail($userId);
        
        Log::info("Obteniendo mallas asignadas al profesor: {$userId}");

        $assignments = DB::table('curriculum_grid_user as cgu')
            ->join('curriculum_grid as cg', 'cgu.curriculum_grid_id', '=', 'cg.id')
            ->join('grade as g', 'cg.grade_id', '=', 'g.id')
            ->join('subject as s', 'cg.subject_id', '=', 's.id')
            ->leftJoin('users as coordinator_user', 'cgu.assigned_by', '=', 'coordinator_user.id')
            ->leftJoin('schools as sch', 'cgu.school_id', '=', 'sch.id')
            ->where('cgu.user_id', $userId)
            ->select([
                'cg.id as curriculum_grid_id',
                'cg.description',
                'g.name as grade_name',
                's.name as subject_name',
                'cgu.assigned_at',
                'sch.name as school_name',
                DB::raw("CONCAT(coordinator_user.first_name, ' ', coordinator_user.last_name) as assigned_by_name"),
            ])
            ->orderBy('cgu.assigned_at', 'desc')
            ->get();

        return response()->json([
            'teacher_id' => $userId,
            'teacher_name' => "{$teacher->first_name} {$teacher->last_name}",
            'assignments' => $assignments,
        ]);
    }

    /**
     * Obtener todos los profesores que tienen una malla asignada
     * GET /curriculum-grid/{id}/teachers
     */
    public function getGridAssignments(Request $request, int $curriculumGridId)
    {
        $coordinator = $request->user();
        
        // Verificar permisos
        if (!$coordinator->isCoordinator() && !$coordinator->isAdmin()) {
            return response()->json([
                'message' => 'Solo coordinadores o administradores pueden ver estas asignaciones'
            ], Response::HTTP_FORBIDDEN);
        }

        $grid = CurriculumGrid::findOrFail($curriculumGridId);

        $teachers = DB::table('curriculum_grid_user as cgu')
            ->join('users as u', 'cgu.user_id', '=', 'u.id')
            ->leftJoin('schools as sch', 'cgu.school_id', '=', 'sch.id')
            ->where('cgu.curriculum_grid_id', $curriculumGridId)
            ->select([
                'u.id as teacher_id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'cgu.assigned_at',
                'cgu.school_id',
                'sch.name as school_name',
            ])
            ->orderBy('cgu.assigned_at', 'desc')
            ->get();

        return response()->json([
            'curriculum_grid_id' => $curriculumGridId,
            'grade_name' => $grid->grade->name ?? null,
            'subject_name' => $grid->subject->name ?? null,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Obtener profesores disponibles para asignar (filtrados por colegio si aplica)
     * GET /teachers/available
     */
    public function getAvailableTeachers(Request $request)
    {
        $schoolId = $request->query('school_id');
        
        $query = User::query()
            ->with(['roles', 'schools'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'profesor');
            });

        // Filtrar por colegio si se especifica
        if ($schoolId) {
            $query->whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            });
        }

        $teachers = $query->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => "{$teacher->first_name} {$teacher->last_name}",
                    'email' => $teacher->email,
                    'schools' => $teacher->schools->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name
                    ]),
                ];
            });

        return response()->json($teachers);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSubjectController extends Controller
{
    // GET /teacher-subjects?teacher_id=X  OR  ?school_id=X
    public function index(Request $request)
    {
        $query = DB::table('teacher_subject as ts')
            ->join('users as u', 'ts.teacher_id', '=', 'u.id')
            ->join('grade as g', 'ts.grade_id', '=', 'g.id')
            ->join('subject as s', 'ts.subject_id', '=', 's.id')
            ->select(
                'ts.id', 'ts.teacher_id', 'ts.grade_id', 'ts.subject_id',
                'ts.school_id', 'ts.created_at',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as teacher_name"),
                'u.email as teacher_email',
                'g.name as grade_name',
                's.name as subject_name'
            );

        if ($request->filled('teacher_id')) {
            $query->where('ts.teacher_id', $request->query('teacher_id'));
        }
        if ($request->filled('school_id')) {
            $query->where('ts.school_id', $request->query('school_id'));
        }
        if ($request->filled('grade_id')) {
            $query->where('ts.grade_id', $request->query('grade_id'));
        }

        return response()->json($query->orderBy('g.name')->orderBy('s.name')->get());
    }

    // POST /teacher-subjects
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isCoordinator() && !$user->isAdmin()) {
            return response()->json(['message' => 'Sin permiso'], 403);
        }

        $validated = $request->validate([
            'teacher_id'  => ['required', 'integer', 'exists:users,id'],
            'grade_id'    => ['required', 'integer', 'exists:grade,id'],
            'subject_id'  => ['required', 'integer', 'exists:subject,id'],
            'school_id'   => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        // Verify the user is actually a teacher
        $teacher = User::with('roles')->findOrFail($validated['teacher_id']);
        $isTeacher = $teacher->roles->contains('name', 'profesor');
        if (!$isTeacher) {
            return response()->json(['message' => 'El usuario no tiene rol de profesor'], 422);
        }

        $exists = DB::table('teacher_subject')
            ->where('teacher_id', $validated['teacher_id'])
            ->where('grade_id', $validated['grade_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ya existe esta asignación'], 422);
        }

        $id = DB::table('teacher_subject')->insertGetId([
            'teacher_id'  => $validated['teacher_id'],
            'grade_id'    => $validated['grade_id'],
            'subject_id'  => $validated['subject_id'],
            'school_id'   => $validated['school_id'] ?? null,
            'assigned_by' => $user->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $record = DB::table('teacher_subject as ts')
            ->join('grade as g', 'ts.grade_id', '=', 'g.id')
            ->join('subject as s', 'ts.subject_id', '=', 's.id')
            ->where('ts.id', $id)
            ->select('ts.*', 'g.name as grade_name', 's.name as subject_name')
            ->first();

        return response()->json($record, 201);
    }

    // DELETE /teacher-subjects/{id}
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user->isCoordinator() && !$user->isAdmin()) {
            return response()->json(['message' => 'Sin permiso'], 403);
        }

        $deleted = DB::table('teacher_subject')->where('id', $id)->delete();
        if (!$deleted) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->noContent();
    }

    // GET /teachers  — list all teachers with their subject assignments
    public function teachers(Request $request)
    {
        $user = $request->user();
        if (!$user->isCoordinator() && !$user->isAdmin()) {
            return response()->json(['message' => 'Sin permiso'], 403);
        }

        $schoolFilter = $request->query('school_id');

        $teacherRole = DB::table('roles')->where('name', 'profesor')->value('id');
        if (!$teacherRole) {
            return response()->json([]);
        }

        $teachersQuery = DB::table('users as u')
            ->join('roles_user as ru', function($j) use ($teacherRole) {
                $j->on('ru.user_id', '=', 'u.id')
                  ->where('ru.role_id', '=', $teacherRole);
            })
            ->whereNull('u.deleted_at')
            ->select('u.id', 'u.first_name', 'u.last_name', 'u.email', 'u.username');

        if ($schoolFilter) {
            $teachersQuery->join('user_school as us', function($j) use ($schoolFilter) {
                $j->on('us.user_id', '=', 'u.id')->where('us.school_id', '=', $schoolFilter);
            });
        }

        $teachers = $teachersQuery->orderBy('u.last_name')->get();

        // Attach subject assignments
        $teacherIds = $teachers->pluck('id')->toArray();
        $assignments = DB::table('teacher_subject as ts')
            ->join('grade as g', 'ts.grade_id', '=', 'g.id')
            ->join('subject as s', 'ts.subject_id', '=', 's.id')
            ->whereIn('ts.teacher_id', $teacherIds)
            ->select('ts.id', 'ts.teacher_id', 'ts.grade_id', 'ts.subject_id', 'g.name as grade_name', 's.name as subject_name')
            ->get()
            ->groupBy('teacher_id');

        $result = $teachers->map(function ($t) use ($assignments) {
            $t->subjects = $assignments->get($t->id, collect())->values();
            return $t;
        });

        return response()->json($result);
    }
}

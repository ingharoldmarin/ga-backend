<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenericCrudController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserSchoolController;
use App\Http\Controllers\Api\CurriculumGridController;
use App\Http\Controllers\Api\CurriculumAssignmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Protected endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Users & Roles CRUD
        Route::post('/users/bulk', [UserController::class, 'bulkStore']); // DEBE IR ANTES del apiResource
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);

        // Admin-only: asignación de roles a usuarios
        Route::middleware('role:admin')->group(function () {
            Route::post('users/{user}/roles/sync', [UserRoleController::class, 'sync']);
            Route::post('users/{user}/roles/attach', [UserRoleController::class, 'attach']);
            Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'detach']);
        });

        // Asignar colegios a usuarios
        Route::post('users/{user}/schools/sync', [UserSchoolController::class, 'sync']);
        Route::post('users/{user}/schools/attach', [UserSchoolController::class, 'attach']);
        Route::delete('users/{user}/schools/{school}', [UserSchoolController::class, 'detach']);

        // Schedule (cronograma) specific routes
        Route::apiResource('schedule', ScheduleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('schedule/batch', [ScheduleController::class, 'batchStore']);

        // ═══════════════════════════════════════════════════════════════════
        // ⭐ MALLA CURRICULAR - ORDEN CORRECTO DE RUTAS
        // ═══════════════════════════════════════════════════════════════════
        // IMPORTANTE: Las rutas específicas ANTES de las rutas con parámetros
        
        // Resumen de mallas
        Route::get('curriculum-grid/summary', [CurriculumGridController::class, 'summary']);
        
        // ⭐ NUEVAS RUTAS DE ASIGNACIÓN (van antes de las rutas genéricas)
        // Obtener mallas asignadas al profesor autenticado
        Route::get('curriculum-grid/assigned', [CurriculumAssignmentController::class, 'getMyAssignments']);
        
        // Obtener profesores disponibles para asignar
        Route::get('teachers/available', [CurriculumAssignmentController::class, 'getAvailableTeachers']);
        
        // Asignar/desasignar mallas (coordinadores y admin)
        Route::post('curriculum-grid/{id}/assign', [CurriculumAssignmentController::class, 'assign']);
        Route::delete('curriculum-grid/{id}/unassign/{userId}', [CurriculumAssignmentController::class, 'unassign']);
        Route::get('curriculum-grid/{id}/teachers', [CurriculumAssignmentController::class, 'getGridAssignments']);
        
        // Obtener mallas de un profesor específico (para coordinadores)
        Route::get('users/{userId}/curriculum-grids', [CurriculumAssignmentController::class, 'getTeacherAssignments']);
        
        // CRUD básico de mallas curriculares
        Route::get('curriculum-grid', [CurriculumGridController::class, 'index']);
        Route::post('curriculum-grid', [CurriculumGridController::class, 'store']);
        Route::get('curriculum-grid/{id}', [CurriculumGridController::class, 'show']);
        Route::put('curriculum-grid/{id}', [CurriculumGridController::class, 'update']);
        Route::patch('curriculum-grid/{id}', [CurriculumGridController::class, 'update']);
        Route::delete('curriculum-grid/{id}', [CurriculumGridController::class, 'destroy']);

        // Generic CRUD routes for whitelisted resources
        Route::get('{resource}', [GenericCrudController::class, 'index']);
        Route::post('{resource}', [GenericCrudController::class, 'store']);
        Route::get('{resource}/{id}', [GenericCrudController::class, 'show'])->whereNumber('id');
        Route::put('{resource}/{id}', [GenericCrudController::class, 'update'])->whereNumber('id');
        Route::patch('{resource}/{id}', [GenericCrudController::class, 'update'])->whereNumber('id');
        Route::delete('{resource}/{id}', [GenericCrudController::class, 'destroy'])->whereNumber('id');
    });
});
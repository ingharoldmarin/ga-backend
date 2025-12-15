<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenericCrudController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserSchoolController;
use App\Http\Controllers\Api\CurriculumGridController;
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
        Route::apiResource('users', UserController::class);
        // Creación masiva (DEBE IR ANTES del apiResource)
        Route::post('/users/bulk', [UserController::class, 'bulkStore']);
        Route::apiResource('roles', RoleController::class);

        // Admin-only: asignación de roles a usuarios
        Route::middleware('role:admin')->group(function () {
            Route::post('users/{user}/roles/sync', [UserRoleController::class, 'sync']);
            Route::post('users/{user}/roles/attach', [UserRoleController::class, 'attach']);
            Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'detach']);
        });

        // Schedule (cronograma) specific routes
        Route::apiResource('schedule', ScheduleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('schedule/batch', [ScheduleController::class, 'batchStore']);

        // ═══════════════════════════════════════════════════════════
        // ⭐ MALLA CURRICULAR - ORDEN CORRECTO DE RUTAS
        // ═══════════════════════════════════════════════════════════
        // IMPORTANTE: Las rutas específicas ANTES de las rutas con parámetros
        
        Route::get('curriculum-grid/summary', [CurriculumGridController::class, 'summary']);
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

        // Asignar colegios a usuarios
        Route::post('users/{user}/schools/sync', [UserSchoolController::class, 'sync']);
        Route::post('users/{user}/schools/attach', [UserSchoolController::class, 'attach']);
        Route::delete('users/{user}/schools/{school}', [UserSchoolController::class, 'detach']);
    });
});
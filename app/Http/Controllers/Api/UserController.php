<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 25), 200);

        $query = User::with(['roles', 'schools']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('school_ids')) {
            $schoolIds = array_filter(array_map('intval', explode(',', $request->query('school_ids'))));
            if (!empty($schoolIds)) {
                $query->whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds));
            }
        }

        if ($request->boolean('exclude_admins')) {
            $query->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'));
        }

        return $query->paginate($perPage);
    }

    public function show(User $user)
    {
        // ⭐ Agregar schools a la respuesta
        return $user->load(['roles', 'schools']);
    }

    public function store(Request $request)
    {
        // Log de datos recibidos
        Log::info('=== CREANDO NUEVO USUARIO ===');
        Log::info('Datos recibidos:', $request->all());

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:6'],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            // ⭐ NUEVO: Validación de school_ids
            'school_ids' => ['nullable', 'array'],
            'school_ids.*' => ['integer', 'exists:schools,id'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
            ]);

            if (!empty($validated['roles'])) {
                $user->roles()->sync($validated['roles']);
            }

            if (!empty($validated['school_ids'])) {
                $user->schools()->sync($validated['school_ids']);
            }

            return $user;
        });

        Log::info('Usuario creado con ID: ' . $user->id);

        // ⭐ IMPORTANTE: Retornar con ambas relaciones
        return response()->json($user->load(['roles', 'schools']), Response::HTTP_CREATED);
    }

    public function update(Request $request, User $user)
    {
        Log::info('=== ACTUALIZANDO USUARIO ===');
        Log::info('User ID: ' . $user->id);
        Log::info('Datos recibidos:', $request->all());

        $validated = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            // ⭐ NUEVO: Validación de school_ids
            'school_ids' => ['nullable', 'array'],
            'school_ids.*' => ['integer', 'exists:schools,id'],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $user->fill(collect($validated)->except(['roles', 'password', 'school_ids'])->all());

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            if (array_key_exists('roles', $validated)) {
                $user->roles()->sync($validated['roles'] ?? []);
            }

            if (array_key_exists('school_ids', $validated)) {
                $user->schools()->sync($validated['school_ids'] ?? []);
            }
        });

        Log::info('Usuario actualizado: ' . $user->id);

        // ⭐ IMPORTANTE: Retornar con ambas relaciones
        return $user->load(['roles', 'schools']);
    }

    public function destroy(User $user)
    {
        $user->delete(); // SoftDelete — el AuditObserver registra la acción automáticamente
        return response()->noContent();
    }

    /**
     * ⭐ NUEVO: Crear múltiples usuarios en batch (para importación masiva eficiente)
     * POST /api/v1/users/bulk
     */
    public function bulkStore(Request $request)
    {
        Log::info('=== INICIO CREACIÓN MASIVA DE USUARIOS ===');
        Log::info('Cantidad de usuarios a crear: ' . count($request->input('users', [])));

        $validated = $request->validate([
            'users' => ['required', 'array', 'min:1', 'max:500'], // Máximo 500 por batch
            'users.*.first_name' => ['required', 'string', 'max:255'],
            'users.*.last_name' => ['required', 'string', 'max:255'],
            'users.*.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'users.*.username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'users.*.password' => ['required', 'string', 'min:6'],
            'users.*.roles' => ['array'],
            'users.*.roles.*' => ['integer', 'exists:roles,id'],
            'users.*.school_ids' => ['nullable', 'array'],
            'users.*.school_ids.*' => ['integer', 'exists:schools,id'],
        ]);

        $createdUsers = [];
        $errors = [];

        foreach ($validated['users'] as $index => $userData) {
            try {
                $user = DB::transaction(function () use ($userData) {
                    $user = User::create([
                        'first_name' => $userData['first_name'],
                        'last_name' => $userData['last_name'],
                        'email' => $userData['email'],
                        'username' => $userData['username'],
                        'password' => Hash::make($userData['password']),
                    ]);

                    if (!empty($userData['roles'])) {
                        $user->roles()->sync($userData['roles']);
                    }

                    if (!empty($userData['school_ids'])) {
                        $user->schools()->sync($userData['school_ids']);
                    }

                    return $user;
                });

                $createdUsers[] = $user->load(['roles', 'schools']);
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'email' => $userData['email'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
                Log::error("Error creando usuario {$index}: " . $e->getMessage());
            }
        }

        Log::info('Usuarios creados exitosamente: ' . count($createdUsers));
        Log::info('Errores: ' . count($errors));

        return response()->json([
            'success' => true,
            'created' => count($createdUsers),
            'failed' => count($errors),
            'users' => $createdUsers,
            'errors' => $errors
        ], Response::HTTP_CREATED);
    }
}
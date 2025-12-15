<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRoleController extends Controller
{
    // Asignar roles a un usuario (reemplaza el conjunto actual)
    public function sync(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);
        $user->roles()->sync($validated['roles']);
        return response()->json($user->load('roles'));
    }

    // Agregar un rol adicional a un usuario (sin quitar los existentes)
    public function attach(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);
        $user->roles()->syncWithoutDetaching([$validated['role_id']]);
        return response()->json($user->load('roles'), Response::HTTP_CREATED);
    }

    // Quitar un rol específico de un usuario
    public function detach(User $user, Role $role)
    {
        $user->roles()->detach($role->id);
        return response()->json($user->load('roles'));
    }
}


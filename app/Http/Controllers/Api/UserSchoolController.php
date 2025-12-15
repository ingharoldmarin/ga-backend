<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSchoolController extends Controller
{
    public function sync(Request $request, User $user)
    {
        $validated = $request->validate([
            'schools' => ['required', 'array'],
            'schools.*' => ['integer', 'exists:schools,id']
        ]);

        $user->schools()->sync($validated['schools']);
        
        return response()->json([
            'message' => 'Colegios sincronizados exitosamente',
            'user' => $user->load('schools')
        ]);
    }
}
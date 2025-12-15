<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        return School::paginate(25);
    }

    public function show(School $school)
    {
        return $school;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit' => 'nullable|string|max:255',
            'resolution' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500'
        ]);

        $school = School::create($validated);

        return response()->json($school, 201);
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'nit' => 'nullable|string|max:255',
            'resolution' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500'
        ]);

        $school->update($validated);

        return $school;
    }

    public function destroy(School $school)
    {
        $school->delete();
        return response()->noContent();
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GenericCrudController extends Controller
{
    /**
     * Map of allowed API resources to their actual database table names.
     * Excludes schedule (cronograma) and pivot/intermediate tables.
     * Adjust keys (URL segments) to your preferred naming.
     */
    private const RESOURCE_TO_TABLE = [
        'schools' => 'schools',
        'subjects' => 'subject',
        'grades' => 'grade',
        'weeks' => 'week',
        'topics' => 'topic',
        'components' => 'component',
        'didactic-units' => 'didactic_unit',
        'standards' => 'standard',
        'competences' => 'competence',
        'tipe-competences' => 'tipe_competence',
        'affirmations-dna-dba' => 'affirmation_dna_dba',
        'evidences-dna-dba' => 'evidence_dna_dba',
        'activities' => 'activity',
        // schedule intentionally excluded
        // pivot tables intentionally excluded
    ];

    private function resolveTableOrAbort(string $resource): string
    {
        $table = self::RESOURCE_TO_TABLE[$resource] ?? null;
        if ($table === null) {
            abort(Response::HTTP_NOT_FOUND, 'Resource not found');
        }
        return $table;
    }

    public function index(Request $request, string $resource)
    {
        $table = $this->resolveTableOrAbort($resource);
        $perPage = (int) min(max((int) $request->query('per_page', 15), 1), 100);
        return DB::table($table)->paginate($perPage);
    }

    public function show(string $resource, int $id)
    {
        $table = $this->resolveTableOrAbort($resource);
        $row = DB::table($table)->where('id', $id)->first();
        if (!$row) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return response()->json($row);
    }

    public function store(Request $request, string $resource)
    {
        $table = $this->resolveTableOrAbort($resource);
        $data = $request->except(['id', 'created_at', 'updated_at']);
        $id = DB::table($table)->insertGetId($data);
        $row = DB::table($table)->where('id', $id)->first();
        return response()->json($row, Response::HTTP_CREATED);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $table = $this->resolveTableOrAbort($resource);
        $data = $request->except(['id', 'created_at', 'updated_at']);
        $exists = DB::table($table)->where('id', $id)->exists();
        if (!$exists) {
            abort(Response::HTTP_NOT_FOUND);
        }
        DB::table($table)->where('id', $id)->update($data);
        $row = DB::table($table)->where('id', $id)->first();
        return response()->json($row);
    }

    public function destroy(string $resource, int $id)
    {
        $table = $this->resolveTableOrAbort($resource);
        $deleted = DB::table($table)->where('id', $id)->delete();
        if ($deleted === 0) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return response()->noContent();
    }
}


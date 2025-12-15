<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumGrid extends Model
{
    use HasFactory;

    protected $table = 'curriculum_grid';

    protected $fillable = [
        'grade_id',
        'subject_id',
        'description',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relaciones principales
     */
    
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relaciones múltiples (muchos a muchos)
     */
    
    public function topics()
    {
        return $this->belongsToMany(
            Topic::class,
            'curriculum_grid_topic',
            'curriculum_grid_id',
            'topic_id'
        )->withTimestamps();
    }

    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'curriculum_grid_component',
            'curriculum_grid_id',
            'component_id'
        )->withTimestamps();
    }

    public function standards()
    {
        return $this->belongsToMany(
            Standard::class,
            'curriculum_grid_standard',
            'curriculum_grid_id',
            'standard_id'
        )->withTimestamps();
    }

    public function competences()
    {
        return $this->belongsToMany(
            Competence::class,
            'curriculum_grid_competence',
            'curriculum_grid_id',
            'competence_id'
        )->withTimestamps();
    }

    public function affirmations()
    {
        return $this->belongsToMany(
            AffirmationDnaDba::class,
            'curriculum_grid_affirmation',
            'curriculum_grid_id',
            'affirmation_id'
        )->withTimestamps();
    }

    public function evidences()
    {
        return $this->belongsToMany(
            EvidenceDnaDba::class,
            'curriculum_grid_evidence',
            'curriculum_grid_id',
            'evidence_id'
        )->withTimestamps();
    }
}
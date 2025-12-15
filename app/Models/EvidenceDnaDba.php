<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceDnaDba extends Model
{
    use HasFactory;
    protected $table = 'evidence_dna_dba';
    protected $fillable = ['name', 'description'];
}
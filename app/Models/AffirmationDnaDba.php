<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffirmationDnaDba extends Model
{
    use HasFactory;
    protected $table = 'affirmation_dna_dba';
    protected $fillable = ['name', 'description'];
}
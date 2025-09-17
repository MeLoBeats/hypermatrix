<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Enseignant extends Model
{
    /** @use HasFactory<\Database\Factories\EnseignantFactory> */
    use HasFactory;

    protected $fillable = [
        "hp_id",
        "nom",
        "prenom",
        "matricule"
    ];

    protected $appends = ['salles'];
    protected $hidden = ['cours'];

    public function cours(): BelongsToMany
    {
        return $this->belongsToMany(Cours::class, "cours_enseignants")->withPivot("active");
    }

    public function getSallesAttribute()
    {
        return $this->cours
            ->pluck('salle')
            ->filter() // évite les nulls si une relation est absente
            ->unique('id')
            ->values();
    }

    public function salles()
    {
        return $this->cours()
            ->with('salle')
            ->get()
            ->pluck('salle')
            ->unique('id')
            ->values();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function cours(): BelongsToMany
    {
        return $this->belongsToMany(Cours::class, "cours_enseignants");
    }
}

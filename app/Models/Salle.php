<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Salle extends Model
{
    /** @use HasFactory<\Database\Factories\SalleFactory> */
    use HasFactory;

    protected $fillable = [
        "hp_id",
        "dorma",
        "libelle"
    ];

    public function casts(): array
    {
        return [
            "dorma" => "array"
        ];
    }

    public function cours(): HasMany
    {
        return $this->hasMany(Cours::class);
    }

    public function enseignants(): HasManyThrough
    {
        return $this->hasManyThrough(Enseignant::class, Cours::class);
    }
}

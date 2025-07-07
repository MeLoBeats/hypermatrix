<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cours extends Model
{
    /** @use HasFactory<\Database\Factories\CoursFactory> */
    use HasFactory;

    protected $fillable = [
        "hp_id",
        "salle_id",
        "date",
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d'
        ];
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }

    public function enseignants(): BelongsToMany
    {
        return $this->BelongsToMany(Enseignant::class, "cours_enseignants");
    }
}

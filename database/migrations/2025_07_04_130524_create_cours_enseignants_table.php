<?php

use App\Models\Cours;
use App\Models\Enseignant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cours_enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cours::class)->constrained()->onDelete('CASCADE');
            $table->foreignIdFor(Enseignant::class)->constrained()->onDelete('CASCADE');
            $table->boolean('active')->default(false)->comment('TRUE si la synchronisation avec Matrix à été faite');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cours_enseignants');
    }
};

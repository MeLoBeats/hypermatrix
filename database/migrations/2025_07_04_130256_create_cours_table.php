<?php

use App\Models\Salle;
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
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string("hp_id");
            $table->foreignIdFor(Salle::class)->constrained()->onDelete('CASCADE');
            $table->date('date');
            $table->timestamps();

            $table->unique(['hp_id', 'salle_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};

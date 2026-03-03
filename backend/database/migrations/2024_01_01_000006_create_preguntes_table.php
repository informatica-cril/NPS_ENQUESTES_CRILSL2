<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquesta_id')->constrained('enquestes')->onDelete('cascade');
            $table->string('text_pregunta');
            $table->text('descripcio')->nullable();
            $table->enum('tipus', [
                'nps', // 0-10 scale
                'escala', // 1-5 or custom scale
                'text_curt',
                'text_llarg',
                'opcio_unica',
                'opcio_multiple',
                'si_no',
                'data',
                'valoracio_estrelles'
            ])->default('text_curt');
            $table->integer('ordre')->default(0);
            $table->boolean('obligatoria')->default(false);
            $table->json('opcions')->nullable(); // For opcio_unica/multiple
            $table->json('configuracio')->nullable(); // Additional config (min, max, etc.)
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['enquesta_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntes');
    }
};

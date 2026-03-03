<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nps_resultats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquesta_id')->constrained('enquestes')->onDelete('cascade');
            $table->foreignId('participacio_id')->constrained('participacions')->onDelete('cascade');
            $table->foreignId('centre_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fisioterapeuta_id')->nullable()->constrained('fisioterapeutes')->onDelete('set null');
            $table->integer('puntuacio'); // 0-10
            $table->enum('categoria', ['detractor', 'passiu', 'promotor']);
            $table->text('comentari')->nullable();
            $table->date('data');
            $table->timestamps();

            $table->index(['enquesta_id', 'data']);
            $table->index(['centre_id', 'data']);
            $table->index(['fisioterapeuta_id', 'data']);
        });

        // Aggregated NPS stats per period
        Schema::create('nps_estadistiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquesta_id')->nullable()->constrained('enquestes')->onDelete('cascade');
            $table->foreignId('centre_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('fisioterapeuta_id')->nullable()->constrained('fisioterapeutes')->onDelete('cascade');
            $table->enum('periode', ['diari', 'setmanal', 'mensual', 'trimestral', 'anual']);
            $table->date('data_inici');
            $table->date('data_fi');
            $table->integer('total_respostes')->default(0);
            $table->integer('promotors')->default(0);
            $table->integer('passius')->default(0);
            $table->integer('detractors')->default(0);
            $table->decimal('nps_score', 5, 2)->nullable(); // -100 to 100
            $table->decimal('mitjana_puntuacio', 4, 2)->nullable();
            $table->timestamps();

            $table->index(['periode', 'data_inici']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nps_estadistiques');
        Schema::dropIfExists('nps_resultats');
    }
};

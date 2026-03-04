<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Participacions = Survey submissions
        Schema::create('participacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquesta_id')->constrained('enquestes')->onDelete('cascade');
            $table->foreignId('pacient_id')->nullable()->constrained('pacients')->onDelete('set null');
            $table->foreignId('fisioterapeuta_id')->nullable()->constrained('fisioterapeutes')->onDelete('set null');
            $table->string('token')->unique(); // For anonymous access
            $table->enum('estat', ['pendent', 'en_curs', 'completada', 'expirada'])->default('pendent');
            $table->timestamp('data_inici')->nullable();
            $table->timestamp('data_completat')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['enquesta_id', 'estat']);
            $table->index(['token']);
        });

        // Respostes = Individual answers
        Schema::create('respostes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participacio_id')->constrained('participacions')->onDelete('cascade');
            $table->foreignId('pregunta_id')->constrained('preguntes')->onDelete('cascade');
            $table->text('valor_text')->nullable();
            $table->integer('valor_numeric')->nullable();
            $table->json('valor_json')->nullable(); // For multiple choice
            $table->timestamps();

            $table->unique(['participacio_id', 'pregunta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respostes');
        Schema::dropIfExists('participacions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquestes', function (Blueprint $table) {
            $table->id();
            $table->string('titol');
            $table->string('slug')->unique();
            $table->text('descripcio')->nullable();
            $table->enum('tipus', ['nps', 'satisfaccio', 'qualitat', 'general'])->default('nps');
            $table->enum('estat', ['esborrany', 'activa', 'tancada', 'arxivada'])->default('esborrany');
            $table->foreignId('centre_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('data_inici')->nullable();
            $table->date('data_fi')->nullable();
            $table->boolean('anonima')->default(false);
            $table->boolean('requereix_autenticacio')->default(false);
            $table->integer('temps_estimat_minuts')->nullable();
            $table->string('imatge_capçalera')->nullable();
            $table->json('configuracio')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estat', 'data_inici', 'data_fi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquestes');
    }
};

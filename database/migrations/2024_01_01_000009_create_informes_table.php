<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes', function (Blueprint $table) {
            $table->id();
            $table->string('titol');
            $table->text('descripcio')->nullable();
            $table->enum('tipus', ['nps', 'fisioterapeuta', 'centre', 'enquesta', 'general'])->default('general');
            $table->foreignId('enquesta_id')->nullable()->constrained('enquestes')->onDelete('set null');
            $table->foreignId('centre_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fisioterapeuta_id')->nullable()->constrained('fisioterapeutes')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('data_inici');
            $table->date('data_fi');
            $table->json('filtres')->nullable();
            $table->json('dades')->nullable(); // Cached report data
            $table->string('fitxer_path')->nullable();
            $table->enum('estat', ['pendent', 'processant', 'completat', 'error'])->default('pendent');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};

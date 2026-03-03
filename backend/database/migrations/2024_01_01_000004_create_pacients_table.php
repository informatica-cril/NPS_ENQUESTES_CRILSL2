<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('centre_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nom');
            $table->string('cognoms');
            $table->string('dni')->nullable();
            $table->string('cip')->nullable(); // Código Identificación Personal sanitaria
            $table->date('data_naixement')->nullable();
            $table->enum('sexe', ['home', 'dona', 'altre'])->nullable();
            $table->string('telefon')->nullable();
            $table->string('email')->nullable();
            $table->string('adreca')->nullable();
            $table->string('poblacio')->nullable();
            $table->string('codi_postal')->nullable();
            $table->date('data_alta');
            $table->date('data_baixa')->nullable();
            $table->boolean('actiu')->default(true);
            $table->boolean('consentiment_rgpd')->default(false);
            $table->timestamp('data_consentiment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dni']);
            $table->index(['cip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacients');
    }
};

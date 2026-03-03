<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitxers', function (Blueprint $table) {
            $table->id();
            $table->string('nom_original');
            $table->string('nom_emmagatzemat');
            $table->string('path');
            $table->string('disk')->default('local'); // local, s3, azure
            $table->string('mime_type');
            $table->unsignedBigInteger('mida'); // bytes
            $table->morphs('fitxerable'); // Polymorphic relation
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitxers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fisioterapeutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('centre_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nom');
            $table->string('cognoms');
            $table->string('num_colegiat')->unique()->nullable();
            $table->string('especialitat')->nullable();
            $table->date('data_alta')->nullable();
            $table->boolean('actiu')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fisioterapeutes');
    }
};

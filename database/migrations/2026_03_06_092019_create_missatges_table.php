<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missatges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fisioterapeuta_id')->constrained('fisioterapeutes')->onDelete('cascade');
            $table->foreignId('emissor_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('emissor_rol', ['fisioterapeuta', 'admin']);
            $table->text('contingut');
            $table->boolean('llegit')->default(false);
            $table->timestamp('llegit_at')->nullable();
            $table->timestamps();
            $table->index(['fisioterapeuta_id']);
            $table->index(['emissor_user_id', 'llegit']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('missatges');
    }
};

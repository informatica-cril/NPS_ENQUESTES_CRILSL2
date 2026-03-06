<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TAULA: admins
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // 2. TAULA: physiotherapists
        Schema::create('physiotherapists', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('territory')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('email');
            $table->index('territory');
        });

        // 3. TAULA: patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('cip')->unique(); // 4 lletres + 10 dígits
            $table->string('dni');
            $table->string('territory')->nullable();
            $table->date('treatment_end_date');
            $table->boolean('survey_completed')->default(false);
            $table->foreignId('physiotherapist_id')->nullable()->constrained('physiotherapists')->onDelete('set null');
            $table->timestamps();
            
            $table->index('cip');
            $table->index('physiotherapist_id');
            $table->index('survey_completed');
            $table->index('territory');
        });

        // 4. TAULA: survey_responses
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            
            // NPS principal (0-10)
            $table->integer('nps_score');
            
            // Indicadors de qualitat (1-10)
            $table->integer('service_quality')->nullable();
            $table->integer('punctuality')->nullable();
            $table->integer('treatment')->nullable();
            $table->integer('perceived_improvement')->nullable();
            $table->integer('communication')->nullable();
            $table->integer('global_experience')->nullable();
            
            // Indicadors booleans
            $table->boolean('duration_adequate')->nullable();
            $table->boolean('session_over_30_min')->nullable();
            
            // Comentaris
            $table->longText('comments')->nullable();
            
            $table->timestamps();
            
            $table->index('patient_id');
            $table->index('created_at');
            $table->index('nps_score');
        });

        // 5. TAULA: email_logs
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->string('patient_name');
            $table->string('email');
            $table->string('status'); // 'sent' o 'error'
            $table->timestamps();
            
            $table->index('patient_id');
            $table->index('created_at');
        });

        // 6. TAULA: app_settings
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
            
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('physiotherapists');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('admins');
    }
};

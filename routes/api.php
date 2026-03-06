<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EnquestaController;
use App\Http\Controllers\Api\PreguntaController;
use App\Http\Controllers\Api\ParticipacioController;
use App\Http\Controllers\Api\NpsController;
use App\Http\Controllers\Api\CentreController;
use App\Http\Controllers\Api\FisioterapeutaController;
use App\Http\Controllers\Api\PacientController;
use App\Http\Controllers\Api\InformeController;
use App\Http\Controllers\Api\MissatgeController;
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/pacient-login', [AuthController::class, 'pacientLogin']);
});
Route::prefix('public')->group(function () {
    Route::get('/enquesta/{slug}', [EnquestaController::class, 'showBySlug']);
    Route::get('/participacio/{token}', [ParticipacioController::class, 'showByToken']);
    Route::post('/participacio/{token}/submit', [ParticipacioController::class, 'submit']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::apiResource('enquestes', EnquestaController::class);
    Route::post('/enquestes/{enquesta}/duplicate', [EnquestaController::class, 'duplicate']);
    Route::get('/enquestes/{enquesta}/estadistiques', [EnquestaController::class, 'estadistiques']);
    Route::apiResource('enquestes.preguntes', PreguntaController::class)->shallow();
    Route::post('/enquestes/{enquesta}/preguntes/reorder', [PreguntaController::class, 'reorder']);
    Route::apiResource('enquestes.participacions', ParticipacioController::class)->shallow();
    Route::post('/enquestes/{enquesta}/participacions/{participacio}/resend', [ParticipacioController::class, 'resend']);
    Route::prefix('nps')->group(function () {
        Route::get('/dashboard', [NpsController::class, 'dashboard']);
        Route::get('/evolucio', [NpsController::class, 'evolucio']);
        Route::get('/per-centre', [NpsController::class, 'perCentre']);
        Route::get('/per-fisioterapeuta', [NpsController::class, 'perFisioterapeuta']);
        Route::get('/comentaris', [NpsController::class, 'comentaris']);
        Route::get('/export', [NpsController::class, 'export']);
    });
    Route::apiResource('centres', CentreController::class);
    Route::get('/centres-map', [CentreController::class, 'map']);
    Route::apiResource('fisioterapeutes', FisioterapeutaController::class);
    Route::get('/fisioterapeutes/{fisioterapeuta}/estadistiques', [FisioterapeutaController::class, 'estadistiques']);
    Route::get('/pacients/sense-enquestes', [PacientController::class, 'senseEnquestes']);
    Route::apiResource('pacients', PacientController::class);
    Route::get('/pacients/{pacient}/historial', [PacientController::class, 'historial']);
    Route::apiResource('informes', InformeController::class)->except(['update']);
    Route::get('/informes/{informe}/download', [InformeController::class, 'download']);
    Route::post('/informes/{informe}/regenerar', [InformeController::class, 'regenerar']);
    Route::prefix('missatges')->group(function () {
        Route::get('/no-llegits', [MissatgeController::class, 'noLlegits']);
        Route::get('/admin/fisios', [MissatgeController::class, 'fisiosPerAdmin']);
        Route::get('/{fisioterapeutaId}', [MissatgeController::class, 'index']);
        Route::post('/{fisioterapeutaId}', [MissatgeController::class, 'store']);
    });
});

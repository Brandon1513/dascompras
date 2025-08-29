<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpedienteApiController;
use App\Http\Controllers\Api\CargaExpedienteApiController;



// Autenticación API (Sanctum)
Route::post('/login',  [AuthController::class, 'login']);

//expedientes
Route::middleware(['auth:sanctum','active','role:administrador|compras'])->group(function () {
    Route::post('/expedientes/carga', [CargaExpedienteApiController::class,'store']);
    Route::post('/expedientes/{expediente}/adjuntos', [CargaExpedienteApiController::class,'append'])
    ->whereNumber('expediente');
    

    Route::post('/logout', [AuthController::class, 'logout']);
});

//consulta expedientes
Route::middleware(['auth:sanctum','active','role:administrador|compras'])->group(function () {
    Route::get('/expedientes', [ExpedienteApiController::class, 'index']);
    Route::get('/expedientes/{expediente}', [ExpedienteApiController::class, 'show'])
        ->whereNumber('expediente');
});
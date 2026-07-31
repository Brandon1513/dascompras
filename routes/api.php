<?php
// routes/api.php
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpedienteApiController;
use App\Http\Controllers\Api\CargaExpedienteApiController;
use App\Http\Controllers\Api\RequisicionApiController;


// Autenticación API (Sanctum)
Route::post('/login',  [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/me', function (Request $r) {
    $u = $r->user()->load('roles:id,name');

    return response()->json([
        'id'    => $u->id,
        'name'  => $u->name,
        'email' => $u->email,
        'roles' => $u->roles->pluck('name')->values(), // ← array de strings
    ]);
});


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


Route::middleware(['auth:sanctum','active','role:administrador|compras'])->group(function () {
    Route::get('/requisiciones', [RequisicionApiController::class, 'index']);
});
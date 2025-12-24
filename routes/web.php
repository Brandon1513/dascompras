<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Requisiciones\Recibir;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\RequisicionController;
use App\Http\Controllers\ExpedienteWebController;
use App\Http\Controllers\DepartamentoGerenteController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//empleados

Route::middleware(['auth','role:administrador'])->group(function () {
    Route::get('/empleados',                [EmpleadoController::class,'index'])->name('empleados.index');
    Route::get('/empleados/create',         [EmpleadoController::class,'create'])->name('empleados.create');
    Route::post('/empleados',               [EmpleadoController::class,'store'])->name('empleados.store');
    Route::get('/empleados/{user}/edit',    [EmpleadoController::class,'edit'])->name('empleados.edit');
    Route::put('/empleados/{user}',         [EmpleadoController::class,'update'])->name('empleados.update');
    Route::patch('/empleados/{user}/toggle',[EmpleadoController::class,'toggle'])->name('empleados.toggle');
    Route::get('/empleados/{user}/resend', fn() => back()->with('success','Correo reenviado.'))
        ->name('empleados.resend');
});
//Departamentos

Route::middleware(['auth','role:administrador'])->group(function () {
    Route::get('/departamentos/gerentes', [DepartamentoGerenteController::class, 'index'])
        ->name('departamentos.gerentes.index');

    Route::get('/departamentos/{departamento}/gerente', [DepartamentoGerenteController::class, 'edit'])
        ->name('departamentos.gerentes.edit');

    Route::put('/departamentos/{departamento}/gerente', [DepartamentoGerenteController::class, 'update'])
        ->name('departamentos.gerentes.update');
});

//expedientes
Route::middleware(['auth','active','role:administrador|compras'])->group(function () {
    // 1) Estáticas primero
    Route::get('/expedientes',                [ExpedienteWebController::class, 'index'])->name('expedientes.index');
    Route::get('/expedientes/carga',          [ExpedienteWebController::class, 'create'])->name('expedientes.carga.create');
    Route::post('/expedientes/carga',         [ExpedienteWebController::class, 'store'])->name('expedientes.carga.store');

    Route::get('/expedientes/{expediente}/adjuntar',  [ExpedienteWebController::class, 'edit'])
    ->whereNumber('expediente')->name('expedientes.edit');
    Route::post('/expedientes/{expediente}/adjuntar', [ExpedienteWebController::class, 'attach'])
    ->whereNumber('expediente')->name('expedientes.attach');

    // 2) Dinámica al final y con restricción numérica
    Route::get('/expedientes/{expediente}',   [ExpedienteWebController::class, 'show'])
        ->whereNumber('expediente') // <-- evita que “carga” coincida
        ->name('expedientes.show');
        Route::post('expedientes/{expediente}/completar-manual',
        [\App\Http\Controllers\ExpedienteWebController::class, 'completeManual']
    )->name('expedientes.complete-manual');

    Route::delete('expedientes/{expediente}/completar-manual',
        [\App\Http\Controllers\ExpedienteWebController::class, 'undoCompleteManual']
    )->name('expedientes.undo-complete-manual');
});

//requis

// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/requisiciones', [RequisicionController::class, 'index'])->name('requisiciones.index');
    Route::get('/requisiciones/crear', [RequisicionController::class, 'create'])->name('requisiciones.create');
    Route::get('/requisiciones/{requisicion}/editar', [RequisicionController::class, 'edit'])->name('requisiciones.edit');

    // PDF ANTES o DESPUÉS de show no afecta (no colisiona con /{requisicion})
    Route::get('/requisiciones/{requisicion}/pdf', [RequisicionController::class, 'pdf'])
        ->name('requisiciones.pdf')->whereNumber('requisicion');

    Route::get('/requisiciones/{requisicion}', [RequisicionController::class,'show'])->name('requisiciones.show');
    Route::get('/requisiciones/{requisicion}/recibir', Recibir::class)->name('requisiciones.recibir');
});





require __DIR__.'/auth.php';

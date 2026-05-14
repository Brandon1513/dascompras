<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Requisiciones\Recibir;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\RequisicionController;
use App\Http\Controllers\ExpedienteWebController;
use App\Http\Controllers\DepartamentoGerenteController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\TipoImpuestoController;
use App\Livewire\Requisiciones\CerrarRequisicion;
use App\Http\Controllers\TipoRetencionController;
use App\Http\Controllers\RequisicionExportController;

use App\Livewire\Requisiciones\RevisarRequisicion;
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
 
    // ── Estáticas PRIMERO (sin parámetros) ────────────────────
    Route::get('/requisiciones',
        [RequisicionController::class, 'index']
    )->name('requisiciones.index');
 
    Route::get('/requisiciones/crear',
        [RequisicionController::class, 'create']
    )->name('requisiciones.create');
 
    // Exportar Excel — DEBE ir antes de /{requisicion} para no colisionar
    Route::get('/requisiciones/exportar',
        [RequisicionExportController::class, 'export']
    )->name('requisiciones.exportar')
     ->middleware('role:compras|administrador');
 
    // ── Con parámetro numérico fijo ───────────────────────────
    Route::get('/requisiciones/{requisicion}/editar',
        [RequisicionController::class, 'edit']
    )->name('requisiciones.edit');
 
    Route::get('/requisiciones/{requisicion}/pdf',
        [RequisicionController::class, 'pdf']
    )->name('requisiciones.pdf')
     ->whereNumber('requisicion');
 
    Route::get('/requisiciones/{requisicion}/recibir',
        Recibir::class
    )->name('requisiciones.recibir');
 
    Route::get('/requisiciones/{requisicion}/revisar',
        RevisarRequisicion::class
    )->name('requisiciones.revisar')
     ->middleware('role:compras|administrador');
 
    Route::get('/requisiciones/{requisicion}/cerrar',
        CerrarRequisicion::class
    )->name('requisiciones.cerrar')
     ->middleware('role:compras|administrador');
 
    // ── Ruta dinámica SIEMPRE AL FINAL ───────────────────────
    Route::get('/requisiciones/{requisicion}',
        [RequisicionController::class, 'show']
    )->name('requisiciones.show');
 
});



Route::middleware(['auth', 'role:administrador|compras'])->prefix('catalogos')->name('catalogos.')->group(function () {
 
    // Unidades de Medida
    Route::get('/unidades',                      [UnidadMedidaController::class, 'index'])  ->name('unidades.index');
    Route::get('/unidades/create',               [UnidadMedidaController::class, 'create']) ->name('unidades.create');
    Route::post('/unidades',                     [UnidadMedidaController::class, 'store'])  ->name('unidades.store');
    Route::get('/unidades/{unidad}/edit',        [UnidadMedidaController::class, 'edit'])   ->name('unidades.edit');
    Route::put('/unidades/{unidad}',             [UnidadMedidaController::class, 'update']) ->name('unidades.update');
    Route::patch('/unidades/{unidad}/toggle',    [UnidadMedidaController::class, 'toggle']) ->name('unidades.toggle');
 
    // Tipos de Impuesto
    Route::get('/impuestos',                     [TipoImpuestoController::class, 'index'])  ->name('impuestos.index');
    Route::get('/impuestos/create',              [TipoImpuestoController::class, 'create']) ->name('impuestos.create');
    Route::post('/impuestos',                    [TipoImpuestoController::class, 'store'])  ->name('impuestos.store');
    Route::get('/impuestos/{impuesto}/edit',     [TipoImpuestoController::class, 'edit'])   ->name('impuestos.edit');
    Route::put('/impuestos/{impuesto}',          [TipoImpuestoController::class, 'update']) ->name('impuestos.update');
    Route::patch('/impuestos/{impuesto}/toggle', [TipoImpuestoController::class, 'toggle']) ->name('impuestos.toggle');

    // Tipos de Retención
    Route::get('/retenciones',                       [TipoRetencionController::class, 'index'])   ->name('retenciones.index');
    Route::get('/retenciones/create',                [TipoRetencionController::class, 'create'])  ->name('retenciones.create');
    Route::post('/retenciones',                      [TipoRetencionController::class, 'store'])   ->name('retenciones.store');
    Route::get('/retenciones/{retencion}/edit',      [TipoRetencionController::class, 'edit'])    ->name('retenciones.edit');
    Route::put('/retenciones/{retencion}',           [TipoRetencionController::class, 'update'])  ->name('retenciones.update');
    Route::patch('/retenciones/{retencion}/toggle',  [TipoRetencionController::class, 'toggle'])  ->name('retenciones.toggle');

 
});


 




require __DIR__.'/auth.php';

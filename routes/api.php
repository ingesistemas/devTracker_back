<?php

use App\Http\Controllers\Api\EpicaController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\GrupoTrabajoController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\RequisitoEpicaController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\TareaController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------
*/
Route::post('auth/refresh', [AuthController::class, 'refrescar'])->middleware('auth:api');

Route::prefix('usuarios')->group(function () {
    //Route::post('/ingresar', [UsuarioController::class, 'ingresar']);
   
    Route::post('/ingresar', [usuarioController::class, 'ingresar']);
    Route::post('/crear-como-admin', [ UsuarioController::class,'crear']);
});

/* Route::get('/dptos', [DptoController::class, 'consultar']);
Route::get('/ciudades', [CiudadController::class, 'consultar']); */



/*
|--------------------------------------------------
| RUTAS PROTEGIDAS (JWT + BD EMPRESA)
|--------------------------------------------------
*/
/* Route::middleware('jwt')->group(function () { 
    Route::get('/usuarios/consultar', [usuarioController::class, 'consultar']);
});
 */
Route::middleware('jwt')->group(function () {

    Route::middleware('empresa.jwt')->group(function () {
        Route::get('/usuarios/consultar', [ UsuarioController::class,'consultar']);
        Route::post('/usuarios/estado', [ UsuarioController::class,'estado']);
        Route::post('/usuarios/crear', [ UsuarioController::class,'crear']);
        Route::post('/usuarios/editar', [ UsuarioController::class,'editar']);
        Route::post('/usuarios/editar-credenciales', [ UsuarioController::class,'editarCredenciales']);
        Route::post('/usuarios/consultar-personales', [ UsuarioController::class,'consultarPersonales']);
        Route::post('/usuarios/editar-personales', [ UsuarioController::class,'editarPersonales']);

        Route::prefix('epicas')->group(function () {
            Route::get('/consultar', [EpicaController::class, 'consultar']);
            Route::post('/consultar-qa', [EpicaController::class, 'consultarQa']);
            Route::get('/consultar-qa-ok', [EpicaController::class, 'consultarQaOk']);
            Route::get('/programar/{id?}', [EpicaController::class, 'consultar']);
            Route::post('/crear', [EpicaController::class, 'crear']);
            Route::post('/editar', [EpicaController::class, 'editar']);
            Route::post('/editar-historia', [EpicaController::class, 'editarHistoria']);
            Route::post('/eliminar', [EpicaController::class, 'eliminar']);
            Route::post('/PDFEpicas', [EpicaController::class, 'generarPdfEpicas']);
           /*  Route::get('/consultar', [rolController::class, 'consultar']);
            Route::post('/crear', [rolController::class, 'crear']); */
        });

        Route::prefix('requisitos')->group(function () {
            Route::POST('/consultar', [RequisitoEpicaController::class, 'consultar']);
            Route::post('/crear', [RequisitoEpicaController::class, 'crear']);
            Route::post('/editar', [RequisitoEpicaController::class, 'editar']);
            Route::post('/eliminar', [RequisitoEpicaController::class, 'eliminar']);
            Route::post('/agregar-tarea', [RequisitoEpicaController::class, 'crearRequisitoTarea']);
            Route::post('/eliminar-tarea', [RequisitoEpicaController::class, 'eliminarRequisitoTarea']);
           /*  Route::get('/consultar', [rolController::class, 'consultar']);
            Route::post('/crear', [rolController::class, 'crear']); */
        });

        Route::prefix('tareas')->group(function () {
            Route::get('/consultar/{id?}/{id_epica}', [TareaController::class, 'consultar']);
            Route::post('/crear', [TareaController::class, 'crear']);
            Route::post('/editar', [TareaController::class, 'editar']);
            Route::post('/cronometro', [TareaController::class, 'cronometro']);
            Route::post('/temporizador', [TareaController::class, 'temporizador']);
            Route::post('/estado', [TareaController::class, 'estado']);
            Route::post('/eliminar', [TareaController::class, 'eliminar']);
            Route::post('/estado-tarea', [TareaController::class, 'estadoTarea']);
            Route::post('/crear-rechazo', [TareaController::class, 'crearRechazo']);
            Route::post('/observacion', [TareaController::class, 'crearObservacion']); 
             Route::post('/editar-observacion', [TareaController::class, 'editarObservacion']);  
            Route::post('/reordenar-tareas', [TareaController::class, 'reordenarTareas']);    
            Route::post('/eliminar-observacion', [TareaController::class, 'eliminarObs']);        
        });

        Route::prefix('proyectos')->group(function () {
            Route::get('/consultar', [ProyectoController::class, 'consultar']);
            Route::post('/crear', [ProyectoController::class, 'crear']); 
            Route::post('/editar', [ProyectoController::class, 'editar']); 
            Route::post('/estado', [ProyectoController::class, 'estado']); 
        });

        Route::prefix('grupos')->group(function () {
            Route::get('/consultar', [GrupoTrabajoController::class, 'consultar']);
            Route::post('/crear', [GrupoTrabajoController::class, 'crear']); 
            Route::post('/editar', [GrupoTrabajoController::class, 'editar']); 
            Route::post('/estado', [GrupoTrabajoController::class, 'estado']); 
        });
        
        
    });

    Route::prefix('roles')->group(function () {
        Route::get('/consultar', [RolController::class, 'consultar']);
        Route::post('/crear', [RolController::class, 'crear']);
    });

    Route::prefix('estados')->group(function(){
        Route::get('/consultar', [EstadoController::class, 'consultar']);
    });
});

Route::middleware('empresa.jwt')->group(function () { 


    Route::prefix('usuarios')->group(function () {
        /* Route::post('/', [UsuarioController::class, 'crear']); */
       
        /* Route::put('/{id}', [UsuarioController::class, 'editar']);
        Route::delete('/{id}', [UsuarioController::class, 'estado']);  */
    });

    /* Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'consultar']);
            Route::post('/', [RoleController::class, 'crear']);
            Route::get('/{id}', [RoleController::class, 'show']);
            Route::put('/{id}', [RoleController::class, 'editar']);
            Route::delete('/{id}', [RoleController::class, 'estado']); 
    });

    Route::prefix('localidades')->group(function () {
        Route::get('/', [LocalidadController::class, 'consultar']);
            Route::post('/', [LocalidadController::class, 'crear']);
            Route::put('/{localidad}', [LocalidadController::class, 'editar']);
            Route::delete('/{id}', [LocalidadController::class, 'estado']);
    });

    Route::prefix('barrios')->group(function () {
        Route::get('/', [BarrioController::class, 'consultar']);
            Route::post('/', [BarrioController::class, 'crear']);
            Route::put('/{localidad}', [BarrioController::class, 'editar']);
            Route::delete('/{id}', [BarrioController::class, 'estado']); 
    });

    Route::prefix('empresas')->group(function () {
        Route::get('/', [EmpresaController::class, 'consultar']);
    }); */
}); 
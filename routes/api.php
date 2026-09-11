<?php

use App\Http\Controllers\Api\EpicaController;
use App\Http\Controllers\Api\GrupoTrabajoController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\TareaController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------
*/

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
            Route::get('/programar/{id?}', [EpicaController::class, 'consultar']);
            Route::post('/crear', [EpicaController::class, 'crear']);
            Route::post('/editar', [EpicaController::class, 'editar']);
            Route::post('/editar-historia', [EpicaController::class, 'editarHistoria']);
           /*  Route::get('/consultar', [rolController::class, 'consultar']);
            Route::post('/crear', [rolController::class, 'crear']); */
        });

        Route::prefix('tareas')->group(function () {
            Route::get('/consultar/{id?}/{id_epica}', [TareaController::class, 'consultar']);
            Route::post('/crear', [TareaController::class, 'crear']);
            Route::post('/cronometro', [TareaController::class, 'cronometro']);
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
        
        /*Route::post('/', [RoleController::class, 'crear']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'editar']);
        Route::delete('/{id}', [RoleController::class, 'estado']); */

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
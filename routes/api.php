<?php

use App\Http\Controllers\Api\epicaController;
use App\Http\Controllers\Api\proyectoController;
use App\Http\Controllers\Api\rolController;
use App\Http\Controllers\Api\usuarioController;
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
            Route::get('/consultar', [epicaController::class, 'consultar']);
            Route::post('/crear', [epicaController::class, 'crear']);
           /*  Route::get('/consultar', [rolController::class, 'consultar']);
            Route::post('/crear', [rolController::class, 'crear']); */
        });

        Route::prefix('proyectos')->group(function () {
            Route::get('/consultar', [proyectoController::class, 'consultar']);
            Route::post('/crear', [proyectoController::class, 'crear']); 
            Route::post('/editar', [proyectoController::class, 'editar']); 
            Route::post('/estado', [proyectoController::class, 'estado']); 
        });
        
        
    });

    Route::prefix('roles')->group(function () {
        Route::get('/consultar', [rolController::class, 'consultar']);
        Route::post('/crear', [rolController::class, 'crear']);
        
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
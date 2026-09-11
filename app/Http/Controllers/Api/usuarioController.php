<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CredencialesUpdateRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PersonalesUpdateRequest;
use App\Http\Requests\UsuarioRequest;
use App\Http\Requests\UsuarioUpdateRequest;
/* use App\Models\empresaModel; */
use App\Models\UsuarioModel;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    public function consultar(Request $request)
    {
        try {
            $id_empresa = $request->id_empresa;

            $usuarios = usuarioModel::with(['empresa:id,nombre,email,activo','rol:id,rol', 'grupoTrabajo:id,grupo'])
            ->select('id', 'email', 'nombre', 'activo', 'id_empresa', 'id_rol', 'id_grupo_trabajo')
            ->where('id_empresa', $id_empresa)->get();

            if ($usuarios->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Usuarios encontrados',
                    'datos' => $usuarios
                ], 200);
            }

            return response()->json([
                'error' => true,
                'mensaje' => 'No se encontraron datos'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function ingresar(loginRequest $request)
    {
        try {
            $email = $request->email;
            $clave = $request->contrasena;
            $usuario = usuarioModel::with(['empresa:id,nombre,email,activo',
            'rol:id,rol'])
                ->select('id', 'email', 'activo', 'id_empresa', 'id_rol', 'contrasena', 'id_epica', 'id_tarea')
                ->where('email', $email)->first();
            
            if(!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)){
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Acceso denegado. Favor verifica las credenciales ingresadas.'
                ], 200);
            }

            if (!$usuario) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Acceso denegado. Favor verifica las credenciales ingresadas.'
                ], 200);
            }

            if (!$usuario->empresa) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'No se encontró una empresa vinculada a el usuario.'
                ], 200);
            }

            if ($usuario->activo != 0) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Acceso denegado, favor verificar si el usuario está activo.'
                ], 200);
            }

            if ($usuario->empresa->activo != 0) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Acceso denegado. Favor verifica si la empresa está activa.'
                ], 200);
            }

            if ($usuario) {
                $conexion = "devtracker".$usuario->id;
                config(['database.connections.empresa_dinamica.database' => $conexion]);
                $usuario->load([
                    'epicaActiva:id,nombre',
                    'tareaActiva:id,detalle,fec_ini,tiempo'
                ]);
                $token = JWTAuth::claims([
                    'id_usuario' => $usuario->id,
                    'id_empresa' => $usuario->id_empresa,
                    'email' => $usuario->email,

                ])->fromUser($usuario);
                // 4️⃣ Respuesta al frontend

                return response()->json([
                    'error' => false,
                    'token'   => $token,
                    'mensaje' => 'Bienvenido a DevTracker.',
                    'user'    => $usuario,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function estado(Request $request)
    {
        try{
            $id = $request->id;
            $usuario = UsuarioModel::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'No se encontró el usuario registrado en el sistema.'
                ], 200);
            }

            $usuario->activo = !$usuario->activo;
            $usuario->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'Usuario actualizado correctamente.',
                'datos' => $usuario
            ], 200);        
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function crear(usuarioRequest $request)
    {
        try{
            $id_empresa = $request->id_empresa;
            $email = $request->email;
            $id_rol = $request->id_rol;
            $nombre = $request->nombre;
            $id_grupo_trabajo = $request->id_grupo_trabajo;
            $contrasena = Hash::make($request->contrasena);

            $busqueda = usuarioModel::with(['empresa:id,nombre,email,activo','rol:id,rol'])
            ->select('id', 'email', 'activo', 'id_empresa', 'id_rol')
            ->where('id_empresa', $id_empresa)
            ->where('email', $email)->first();

            if ($busqueda) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Este email ya se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }
            $usuario = usuarioModel::create($request->validated());
            $usuario->makeHidden('contrasena');
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $usuario
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function editar(usuarioUpdateRequest $request)
    {
        try{
            $id_empresa = $request->id_empresa;
             $id = $request->id;

            $usuario = usuarioModel::with(['empresa:id,nombre,email,activo','rol:id,rol'])
            ->select('id', 'email', 'activo', 'id_empresa', 'id_rol', 'id_grupo_trabajo')
            ->where('id_empresa', $id_empresa)
            ->where('id', $id)->first();

            if (!$usuario) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Este usuario no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $usuario->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $usuario
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function editarPersonales(personalesUpdateRequest $request)
    {
        try{
            $id_empresa = $request->id_empresa;
            $id = $request->id;

            $usuario = usuarioModel::with(['empresa:id,nombre,email,activo','rol:id,rol'])
            ->select('id', 'email', 'activo', 'id_empresa', 'id_rol')
            ->where('id_empresa', $id_empresa)
            ->where('id', $id)->first();

            if (!$usuario) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Este usuario no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $usuario->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $usuario
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function consultarPersonales(Request $request)
    {
        try {
            $id_empresa = $request->id_empresa;
            $id = $request->id;
            $usuario = usuarioModel::select('id', 'email', 'nombre', 'activo', 'id_empresa')
            ->where('id_empresa', $id_empresa)
            ->where('id', $id)->first();

            if ($usuario) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Usuario encontrado.',
                    'datos' => $usuario
                ], 200);
            }

            return response()->json([
                'error' => true,
                'mensaje' => 'No se encontraron datos'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function editarCredenciales(credencialesUpdateRequest $request)
    {
        try{
            $datos = $request->validated();
            $id_empresa = $request->id_empresa;
            $id_usuario_actual = $request->id_usuario_actual;
            $contrasena = $request->contrasena;

            $usuario = usuarioModel::with(['empresa:id,nombre,email,activo','rol:id,rol'])
            ->select('id', 'email', 'activo', 'id_empresa', 'id_rol', 'contrasena')
            ->where('id_empresa', $id_empresa)
            ->where('id', $id_usuario_actual)->first();

            if (!$usuario || !Hash::check($contrasena, $usuario->contrasena)) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'La contraseña actual no es correcta.'
                ], 200);
            }

            if (!$usuario) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Este usuario no se encuentra registrado en el sistema o no estas autorizado para el proceso de cambio de contraseñas.',
                    'datos' => []
                ], 200);
            }

            $usuario->update(['contrasena' => Hash::make($datos['confirmar'])]);

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $usuario
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}

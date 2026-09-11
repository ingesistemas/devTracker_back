<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UsuarioModel extends Model implements JWTSubject
{
    use LogsActivity;
    protected $table = 'usuarios';
    protected $connection = 'mysql';
    protected $fillable = [
        'rol',
        'email',
        'nombre',
        'contrasena',
        'id_empresa',
        'id_rol',
        'id_grupo_trabajo',
        'id_epica',
        'id_tarea',
        'activo',
        'created_at'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['email', 'contrasena', 'id_empresa', 'id_grupo_trabajo', 'activo'])
        ->logOnlyDirty();
    }

    public function empresa()
    {
        return $this->belongsTo(empresaModel::class, 'id_empresa', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(rolModel::class, 'id_rol', 'id');
    }

    public function grupoTrabajo()
    {
        return $this->belongsTo(grupoTrabajoModel::class, 'id_grupo_trabajo', 'id');
    }


    public function proyectos()
    {
        return $this->hasMany(proyectoModel::class, 'id_usuario', 'id');
    }

    // Épicas creadas por este usuario
    public function epicasCreadas()
    {
        return $this->hasMany(EpicaModel::class, 'id_usu_creo', 'id');
    }

    // Épicas asignadas a este usuario
    public function epicasAsignadas()
    {
        return $this->hasMany(EpicaModel::class, 'id_usu_asig', 'id');
    }

    /**
     * Épica activa vinculada al usuario
     */
    public function epicaActiva()
    {
        return $this->belongsTo(EpicaModel::class, 'id_epica', 'id');
    }

    /**
     * Tarea activa vinculada al usuario
     */
    public function tareaActiva()
    {
        return $this->belongsTo(TareaModel::class, 'id_tarea', 'id');
    }
}

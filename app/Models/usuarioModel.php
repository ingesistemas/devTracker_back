<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Tymon\JWTAuth\Contracts\JWTSubject;

class usuarioModel extends Model implements JWTSubject
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
        ->logOnly(['email', 'contrasena', 'id_empresa', 'activo'])
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
}

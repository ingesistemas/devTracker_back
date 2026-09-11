<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Tymon\JWTAuth\Contracts\JWTSubject;

class EmpresaModel extends Model implements JWTSubject
{
    use LogsActivity;
    protected $table = 'empresas';
    //protected $appends = ['rol'];
    protected $connection = 'mysql';
    protected $fillable = [
        'nombre',
        'direccion',
        'id_ciudad',
        'email',
        'telefonos',
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
            ->logOnly(['nombre', 'direccion', 'id_ciudad', 'email', 'telefonos', 'activo'])
            ->logOnlyDirty();
    }

    public function usuarios()
    {
        return $this->hasMany(usuarioModel::class, 'id_empresa', 'id');
    }
}

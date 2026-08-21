<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class proyectoModel extends Model
{
    protected $table = 'proyectos';
    protected $connection = 'empresa_dinamica';
    protected $fillable = [
        'proyecto',
        'id_usuario',
        'activo',
        'created_at'
    ];

    public function usuario()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usuario', 'id');
    }

    public function epicas()
    {
         return $this->hasMany(epicaModel::class, 'id_proyecto', 'id');
    }
}

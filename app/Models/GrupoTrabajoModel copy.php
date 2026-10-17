<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoTrabajoModel extends Model
{
    protected $table = 'grupos_trabajos';
    protected $connection = 'empresa_dinamica';
    protected $fillable = [
        'grupo',
        'id_jefe',
        'detalle',
        'id_usu_creo',
        'activo'
    ];

    public function jefe(){
        return $this->belongsTo(usuarioModel::class, 'id_jefe', 'id');
    }

    public function creador()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usu_creo', 'id');
    }
}

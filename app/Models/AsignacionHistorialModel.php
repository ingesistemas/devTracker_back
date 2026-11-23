<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionHistorialModel extends Model
{
    protected $table = 'historial_asignaciones';
    protected $connection = 'empresa_dinamica';

    // Descomenta la siguiente línea si la tabla NO tiene columna updated_at:
    // const UPDATED_AT = null;

    protected $fillable = [
        'id_tarea',
        'id_usu_creo',
        'id_reasig',
        'created_at'
    ];

    public function tarea()
    {
        return $this->belongsTo(TareaModel::class, 'id_tarea', 'id');
    }

    public function usuarioCreo()
    {
        return $this->belongsTo(User::class, 'id_usu_creo', 'id');
    }

    public function usuarioReasignado()
    {
        return $this->belongsTo(User::class, 'id_reasig', 'id');
    }
}
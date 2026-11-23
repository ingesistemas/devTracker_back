<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitoTareaModel extends Model
{
    protected $table = 'requisitos_tareas';
    protected $connection = 'empresa_dinamica';
    protected $fillable = [
        'id_requisito',
        'id_tarea',
        'porcentaje',
        'grado_complejidad',
        'checked_id',
        'created_at',
        'updated_at'
    ];

    public function tareas()
    {
        return $this->hasMany(TareaModel::class, 'id_tarea', 'id');
    }

    public function tarea()
    {
        return $this->belongsTo(TareaModel::class, 'id_tarea', 'id');
    }

    public function requisito()
    {
        return $this->belongsTo(RequisitoModel::class, 'id_requisito', 'id');
    }

    public function epicas()
    {
        return $this->hasMany(EpicaModel::class, 'id_tarea', 'id');
    }
}

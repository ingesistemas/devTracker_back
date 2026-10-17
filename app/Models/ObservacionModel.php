<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservacionModel extends Model
{
    protected $table = 'observaciones';
    protected $connection = 'empresa_dinamica';
    protected $fillable = [
        'id_tarea',
        'id_usuario',
        'observacion'
    ];

    public function tarea(){
        return $this->belongsTo(TareaModel::class, 'id_tarea', 'id');
    }

    public function creador()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usuario', 'id');
    }
}

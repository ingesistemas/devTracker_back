<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoModel extends Model
{
    protected $table = 'estados';
    protected $connection = 'mysql';
    protected $fillable = [
        'estado',
        'detalle',
        'permiso',
        'created_at'
    ];

    public function epicas()
    {
        return $this->hasMany(epicaModel::class, 'id_estado', 'id');
    }
}

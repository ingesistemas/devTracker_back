<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitoModel extends Model
{
    protected $table = 'requisitos_epicas';
    protected $connection = 'empresa_dinamica';
    protected $fillable = [
        'requisito',
        'id_epica',
        'calidad',
        'desarrollo',
        'created_at'
    ];

    public function epica()
    {
         // Un requisito pertenece a una épica
         return $this->belongsTo(EpicaModel::class, 'id_epica', 'id');
    }
}

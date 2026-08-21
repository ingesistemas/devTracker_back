<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpicaModel extends Model
{
    protected $table = 'epicas';
    protected $connection = 'empresa_dinamica';
    protected $appends = ['nom_prioridad'];
    protected $casts = [
        'f_pentrega' => 'date',
        'fec_ini'    => 'date',
        'f_rentrega' => 'date',
        'id_prioridad' => 'integer'
    ];
    protected $fillable = [
        'nombre', 
        'detalle',
        'id_usu_creo', // ID usuario quién lo creó
        'id_usu_asig', // ID usuario a quién se le asigna inicialmente
        'f_pentrega',
        'fec_ini', // Fecha en que se inicia el desarrollo de la épica
        'fec_fin', // Fecha en que se finaliza el desarrollo de la épica
        'f_rentrega', // Fecha real de la entrega
        'id_proyecto', // ID del proyecto o licencia
        'id_estado', // Estado de la épica
        'id_prioridad'
    ];

    public function estado()
    {
        return $this->belongsTo(estadoModel::class, 'id_estado', 'id');
    }

    public function proyecto()
    {
        return $this->belongsTo(proyectoModel::class, 'id_proyecto', 'id');
    }

    public function usuarioCreo()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usu_creo', 'id');
    }

    public function usuarioAsignado()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usu_asig', 'id');
    }

    public function getNomPrioridadAttribute(): string
    {
        $prioridades = [
            1 => 'Crítica',
            2 => 'Alta',
            3 => 'Media',
            4 => 'Baja'
        ];

        // Accedemos directamente al array de atributos del modelo
        $id = $this->attributes['id_prioridad'] ?? 3;

        return $prioridades[$id] ?? 'Media';
    }
}

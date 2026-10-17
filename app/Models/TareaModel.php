<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TareaModel extends Model
{
    protected $table = 'tareas';
    protected $connection = 'empresa_dinamica';
    protected $appends = ['nom_prioridad', 'nom_complejidad'];
    protected $casts = [
        'f_pentrega' => 'date',
        'f_rentrega'    => 'date',
        'fec_ini'   => 'datetime',
        'fec_fin'   => 'datetime'
    ];
    protected $fillable = [
        'nombre',
        'orden',
        'detalle',
        'id_epica',
        'f_pentrega',
        'f_rentrega', 
        'fec_ini', 
        'fec_fin',
        'tiempo',
        'id_estado', 
        'cronometro',
        'id_prioridad',
        'id_complejidad',
        'id_reasig',
        'calidad',
        'id_usu_creo', 
        'activo'
    ];

    // Incluye 'url_pdf' automáticamente cuando el modelo se convierte a JSON
    


    public function estado()
    {
        return $this->belongsTo(estadoModel::class, 'id_estado', 'id');
    }

    public function epica()
    {
        return $this->belongsTo(EpicaModel::class, 'id_epica', 'id');
    }

    public function usuarioCreo()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usu_creo', 'id');
    }

    public function usuarioAsignado()
    {
        return $this->belongsTo(usuarioModel::class, 'id_reasig', 'id');
    }

    public function getNomPrioridadAttribute(): string
    {
        $prioridades = [
            4 => 'Crítica',
            3 => 'Alta',
            2 => 'Media',
            1 => 'Baja'
        ];

        // Accedemos directamente al array de atributos del modelo
        $id = $this->attributes['id_prioridad'] ?? 3;

        return $prioridades[$id] ?? 'Media';
    }

    public function getNomComplejidadAttribute(): string
    {
        $complejidades = [
            4 => 'Muy alta',
            3 => 'Alta',
            2 => 'Media',
            1 => 'Baja'
        ];

        // Accedemos directamente al array de atributos del modelo
        $id = $this->attributes['id_complejidad'] ?? '';

        return $complejidades[$id] ?? 'Media';
    }

    // En TareaModel.php
    public function usuariosConEstaTareaActiva()
    {
        // Una tarea puede estar seleccionada como "activa" por muchos usuarios
        return $this->hasMany(UsuarioModel::class, 'id_tarea', 'id');
    }

    public function observaciones()
    {
        return $this->hasMany(ObservacionModel::class, 'id_tarea', 'id');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialTareaModel::class, 'id_tarea', 'id');
    }
}

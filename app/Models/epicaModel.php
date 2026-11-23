<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EpicaModel extends Model
{
    protected $table = 'epicas';
    protected $connection = 'empresa_dinamica';
    protected $appends = ['nom_prioridad', 'url_pdf', 'url_video'];
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
        'id_prioridad',
        'ruta_pdf',
        'ruta_video',
        'historia',
        'modelo'
    ];

    // Incluye 'url_pdf' automáticamente cuando el modelo se convierte a JSON
    
    public function tareas()
    {
        // Reemplaza 'id_epica' si la llave foránea en la tabla tareas tiene otro nombre
        return $this->hasMany(TareaModel::class, 'id_epica', 'id');
    }

    public function requisitos(){
        return $this->hasMany(RequisitoModel::class, 'id_epica', 'id');
    }
    
    // Accessor para devolver la URL completa
    public function getUrlPdfAttribute()
    {
       return $this->ruta_pdf ? asset('storage/' . $this->ruta_pdf) : null;
    }

    // Accessor para Video
    public function getUrlVideoAttribute()
    {
        return $this->ruta_video ? asset('storage/' . $this->ruta_video) : null;
    }

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
            4 => 'Crítica',
            3 => 'Alta',
            2 => 'Media',
            1 => 'Baja'
        ];

        // Accedemos directamente al array de atributos del modelo
        $id = $this->attributes['id_prioridad'] ?? 3;

        return $prioridades[$id] ?? 'Media';
    }

    public function usuariosConEstaEpicaActiva()
    {
        // Una épica puede estar seleccionada como "activa" por muchos usuarios
        return $this->hasMany(UsuarioModel::class, 'id_epica', 'id');   
    }
}

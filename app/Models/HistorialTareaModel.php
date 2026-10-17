<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialTareaModel extends Model
{
    protected $table = 'historial_estados_tareas';
    protected $appends = ['url_pdf', 'url_video'];
    protected $connection = 'empresa_dinamica';
    protected $fillable = [
        'id_tarea',
        'detalle',
        'ruta_pdf',
        'ruta_video',
        'id_estado_ini',
        'id_estado_fin',
        'id_usuario'
    ];

    public function tarea(){
        return $this->belongsTo(TareaModel::class, 'id_tarea', 'id');
    }
    
    public function creador()
    {
        return $this->belongsTo(usuarioModel::class, 'id_usuario', 'id');
    }

    public function estadoInicial()
    {
        return $this->belongsTo(EstadoModel::class, 'id_estado_ini', 'id');
    }

    public function estadoFinal()
    {
        return $this->belongsTo(EstadoModel::class, 'id_estado_fin', 'id');
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
}

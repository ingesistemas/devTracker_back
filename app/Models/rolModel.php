<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolModel extends Model
{
    protected $table = 'roles';
    protected $connection = 'mysql';
    protected $fillable = [
        'rol',
        'created_at'
    ];

    public function usuarios()
    {
        return $this->hasMany(usuarioModel::class, 'id_rol', 'id');
    }
}

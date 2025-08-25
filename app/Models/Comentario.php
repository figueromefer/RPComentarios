<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    // Tabla y llave primaria personalizados
    protected $connection = 'legacy';   
    protected $table = 'T_COMENTARIOS';
    protected $primaryKey = 'TCO_PK_COMENTARIO';
    public $incrementing = true;      // cambia a false si la PK no es autoincremental
    protected $keyType = 'int';       // ajusta a 'string' si corresponde

    public $timestamps = false;       // pon true si tu tabla tiene created_at/updated_at

    // Campos editables
    protected $fillable = [
        'TCO_COMENTARIO',
        'TCO_FK_ESTADO_PUBLICACIONES',
    ];

    // (Opcional) Relación a estados si existe una tabla de estados
    // public function estado()
    // {
    //     return $this->belongsTo(EstadoPublicacion::class, 'TCO_FK_ESTADO_PUBLICACIONES', 'ESP_PK_ESTADO');
    // }
}

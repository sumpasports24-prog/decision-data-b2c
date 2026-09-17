<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Banco, cooperativa u otra entidad reportante. Normaliza lo que antes era
 * `consultas.entidad_nombre` como texto libre repetido en cada fila.
 */
class Entidad extends Model
{
    use HasFactory;

    // Eloquent pluraliza "Entidad" al inglés ("entidads"); la tabla real es
    // "entidades" (ver migración).
    protected $table = 'entidades';

    protected $fillable = [
        'nombre',
        'tipo',
        'email_contacto',
    ];

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }
}

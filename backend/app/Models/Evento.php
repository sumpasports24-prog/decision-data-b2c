<?php

namespace App\Models;

use App\Domain\Casos\Exceptions\BitacoraInmutableException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * La bitácora del caso. Append-only por diseño: es la evidencia del motor de
 * casos y no admite ni update ni delete, ni siquiera desde consola.
 */
class Evento extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'caso_id',
        'actor',
        'tipo',
        'carga',
        'ocurrio_en',
    ];

    protected $casts = [
        'carga' => 'array',
        'ocurrio_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new BitacoraInmutableException('modificar un evento existente');
        });

        static::deleting(function () {
            throw new BitacoraInmutableException('eliminar un evento');
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }
}

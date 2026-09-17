<?php

namespace App\Models;

use App\Domain\Casos\Exceptions\BitacoraInmutableException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Documento generado como evidencia del caso (p. ej. la oposición redactada).
 * Igual que los eventos, es inmutable una vez creado.
 */
class Documento extends Model
{
    use HasFactory;

    const UPDATED_AT = null;
    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'caso_id',
        'tipo',
        'contenido',
        'generado_por',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new BitacoraInmutableException('modificar un documento generado');
        });

        static::deleting(function () {
            throw new BitacoraInmutableException('eliminar un documento generado');
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }
}

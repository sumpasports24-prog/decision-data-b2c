<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'persona_id',
        'entidad_id',
        'motivo',
        'consultada_en',
        'reconocida',
    ];

    protected $casts = [
        'consultada_en' => 'datetime',
        'reconocida' => 'boolean',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function entidad(): BelongsTo
    {
        return $this->belongsTo(Entidad::class);
    }

    public function caso(): HasOne
    {
        return $this->hasOne(Caso::class);
    }

    /**
     * Compatibilidad hacia el resto del dominio (Centinela, Gestor,
     * redactores, tests): todos leen `$consulta->entidad_nombre` como si
     * fuera una columna propia. Internamente ya no lo es — vive en
     * `entidades.nombre` — pero cambiar cada punto de lectura no aporta
     * nada; el accessor es el lugar correcto para absorber ese cambio.
     */
    protected function entidadNombre(): Attribute
    {
        return Attribute::get(fn () => $this->entidad?->nombre);
    }
}

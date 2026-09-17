<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'persona_id',
        'entidad_nombre',
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

    public function caso(): HasOne
    {
        return $this->hasOne(Caso::class);
    }
}

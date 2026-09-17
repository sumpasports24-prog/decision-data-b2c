<?php

namespace App\Models;

use App\Domain\Casos\CasoEstado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Caso extends Model
{
    use HasFactory;

    const TIPO_CONSULTA_NO_RECONOCIDA = 'consulta_no_reconocida';

    protected $fillable = [
        'codigo',
        'persona_id',
        'consulta_id',
        'tipo',
        'estado',
        'abierto_en',
        'vence_en',
    ];

    protected $casts = [
        'estado' => CasoEstado::class,
        'abierto_en' => 'datetime',
        'vence_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $caso) {
            $caso->codigo ??= 'CASO-'.strtoupper((string) Str::ulid());
        });
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function consentimientos(): HasMany
    {
        return $this->hasMany(Consentimiento::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class)->orderBy('ocurrio_en');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Consentimiento firmado y vigente para un alcance específico (caso + entidad).
     * Es la única puerta de entrada hacia `en_gestion`.
     */
    public function consentimientoVigente(string $alcance): ?Consentimiento
    {
        return $this->consentimientos()
            ->where('alcance', $alcance)
            ->whereNotNull('firmado_en')
            ->whereNull('revocado_en')
            ->latest('firmado_en')
            ->first();
    }

    public function registrarEvento(string $actor, string $tipo, array $carga = []): Evento
    {
        return $this->eventos()->create([
            'actor' => $actor,
            'tipo' => $tipo,
            'carga' => $carga,
            'ocurrio_en' => now(),
        ]);
    }
}

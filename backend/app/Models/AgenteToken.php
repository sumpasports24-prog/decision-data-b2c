<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Token de alcance limitado por caso y expirable que consumen las 6
 * herramientas del agente (ver AgenteHerramientasController). Nunca da
 * acceso a la base ni a la API completa: solo a las rutas /api/agente/*
 * para el caso_id exacto por el que se emitió, y solo mientras no expire.
 */
class AgenteToken extends Model
{
    protected $fillable = ['caso_id', 'token_hash', 'actor', 'abilities', 'expira_en', 'usado_en'];

    protected $casts = [
        'abilities' => 'array',
        'expira_en' => 'datetime',
        'usado_en' => 'datetime',
    ];

    public static function emitir(Caso $caso, string $actor, array $abilities, int $minutos = 15): string
    {
        $plano = Str::random(64);

        self::create([
            'caso_id' => $caso->id,
            'token_hash' => hash('sha256', $plano),
            'actor' => $actor,
            'abilities' => $abilities,
            'expira_en' => now()->addMinutes($minutos),
        ]);

        return $plano;
    }

    public static function resolver(string $plano): ?self
    {
        return self::where('token_hash', hash('sha256', $plano))->first();
    }

    public function estaVigente(): bool
    {
        return $this->expira_en->isFuture();
    }

    public function puede(string $ability): bool
    {
        return in_array($ability, $this->abilities, true);
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }
}

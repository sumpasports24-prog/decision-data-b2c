<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * La persona es la única entidad autenticable de la plataforma: no existe un
 * modelo `User` genérico, porque este producto es B2C y el usuario final es
 * siempre el titular de la información, nunca un operador interno.
 */
class Persona extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'cedula_hash',
        'nombre',
        'telefono_e164',
        'identidad_verificada_en',
    ];

    protected $casts = [
        'identidad_verificada_en' => 'datetime',
    ];

    /**
     * Hashea una cédula para almacenarla. La cédula en claro nunca se persiste.
     */
    public static function hashCedula(string $cedula): string
    {
        return hash('sha256', config('app.key').'|'.$cedula);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class);
    }
}

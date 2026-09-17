<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consentimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'caso_id',
        'alcance',
        'texto_version',
        'firmado_en',
        'revocado_en',
        'canal',
    ];

    protected $casts = [
        'firmado_en' => 'datetime',
        'revocado_en' => 'datetime',
    ];

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function estaVigente(): bool
    {
        return $this->firmado_en !== null && $this->revocado_en === null;
    }
}

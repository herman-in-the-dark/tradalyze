<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'status',
        'file_name',
        'file_size_bytes',
        'fills_imported',
        'instruments_created',
        'positions_created',
        'message',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'fills_imported' => 'integer',
            'instruments_created' => 'integer',
            'positions_created' => 'integer',
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FonnteWebhook extends Model
{
    protected $fillable = [
        'phone',
        'message',
        'attachment',
        'event',
        'fonnte_type',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
        ];
    }
}

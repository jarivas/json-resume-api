<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $model
 * @property string $input
 * @property array|null $metadata
 */
class AiRequest extends Model
{
    use HasFactory;

    protected $table = 'ai_requests';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}

<?php

namespace App\Models;

use Database\Factories\InterestFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property array $keywords
 */
class Interest extends Model
{
    /** @use HasFactory<InterestFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];
}

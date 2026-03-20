<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $language
 * @property string $fluency
 */
class Language extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'language',
        'fluency',
    ];
}

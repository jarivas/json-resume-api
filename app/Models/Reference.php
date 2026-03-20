<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $reference
 */
class Reference extends Model
{
    /** @use HasFactory<\Database\Factories\ReferenceFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'reference',
    ];
}

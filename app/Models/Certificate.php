<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property Carbon $date
 * @property string $issuer
 * @property string $url
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Certificate extends Model
{
    /** @use HasFactory<\Database\Factories\CertificateFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'date',
        'issuer',
        'url',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(
            Basic::class,
            'basic_certificates',
            'certificate_id',
            'basic_id'
        );
    }
}

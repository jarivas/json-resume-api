<?php

namespace App\Models;

use Database\Factories\CertificateFactory;
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
 */
class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
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

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'certificate_skill');
    }
}

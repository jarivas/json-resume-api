<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'issuer' => $this->faker->company(),
            'date' => $this->faker->date(),
            'url' => $this->faker->url(),
        ];
    }
}

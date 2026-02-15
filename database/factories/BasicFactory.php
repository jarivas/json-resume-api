<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Model\Location;
use App\Helpers\Model\Profile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Basic>
 */
class BasicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = new Location([
            'address' => $this->faker->address,
            'postalCode' => $this->faker->postcode,
            'city' => $this->faker->city,
            'countryCode' => $this->faker->countryCode
        ]);

        $profile = new Profile([
            'network' => $this->faker->word,
            'username' => $this->faker->userName,
            'url' => $this->faker->url,
        ]);

        return [
            'name' => $this->faker->name,
            'label' => $this->faker->word,
            'email' => $this->faker->safeEmail,
            'phone'=> $this->faker->phoneNumber,
            'url' => $this->faker->url,
            'summary' => $this->faker->sentence,
            'location' => $location,
            'profiles' => collect([$profile]),
        ];
    }
}

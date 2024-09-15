<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role_id' => rand(2, 3),
            'type' => 'user',
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'formattedPhone' => null,
            'phone' => null,
            'google2fa_secret' => null,
            'defaultCountry' => null,
            'carrierCode' => null,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('123456'),
            'phrase' => null,
            'address_verified' => 0,
            'identity_verified' => 0,
            'status' => $this->faker->randomElement(['Active', 'Inactive', 'Suspended']),
            'remember_token' => null,
            'picture' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

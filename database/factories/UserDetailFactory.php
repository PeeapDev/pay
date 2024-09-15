<?php

namespace Database\Factories;

use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDetail>
 */
class UserDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
            'country_id' => rand(1, 100),
            'email_verification' => 0,
            'phone_verification_code' => null,
            'two_step_verification_type' => 'disabled',
            'two_step_verification_code' => null,
            'two_step_verification' => 0,
            'last_login_at' => null,
            'last_login_ip' => $this->faker->ipv4,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'address_1' => $this->faker->streetAddress,
            'address_2' => $this->faker->secondaryAddress,
            'default_currency' => 1,
            'timezone' => $this->faker->timezone,
        ];
    }
}

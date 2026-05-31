<?php

namespace Database\Factories\User;

use App\Models\User\ProfessionalProfile;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfessionalProfile>
 */
class ProfessionalProfileFactory extends Factory
{
    protected $model = ProfessionalProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->professional(),
            'bio' => fake()->paragraph(),
            'avg_rating' => 0,
            'reviews_count' => 0,
            'is_verified' => false,
        ];
    }
}

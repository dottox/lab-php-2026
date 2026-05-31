<?php

namespace Database\Factories\Company;

use App\Models\Company\Company;
use App\Models\User\ProfessionalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'professional_id' => ProfessionalProfile::factory(),
            'commercial_name' => fake()->company(),
            'legal_name' => fake()->company(),
            'tax_id' => fake()->numerify('###########'),
            'contact_info' => [
                'email' => fake()->safeEmail(),
            ],
            'is_private' => false,
        ];
    }
}

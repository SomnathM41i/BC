<?php

namespace Database\Factories;

use App\Models\Contractor;
use App\Models\ContractorType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contractor>
 */
class ContractorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('+91 ##########'),
            'contractor_type_id' => ContractorType::query()->first()?->id
                ?? ContractorType::create(['name' => 'General', 'is_active' => true])->id,
            'notes' => null,
            'is_active' => true,
        ];
    }
}

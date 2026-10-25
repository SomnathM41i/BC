<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'email' => fake()->safeEmail(),
            'service' => 'residential-construction',
            'message' => fake()->paragraph(),
            'locale' => 'en',
            'status' => Inquiry::STATUS_NEW,
        ];
    }
}

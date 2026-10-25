<?php

namespace Database\Factories;

use App\Models\PortfolioProject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortfolioProject>
 */
class PortfolioProjectFactory extends Factory
{
    protected $model = PortfolioProject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'title_mr' => null,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'category' => 'residential',
            'location' => 'Sangli district',
            'location_mr' => null,
            'summary' => fake()->sentence(14),
            'summary_mr' => null,
            'description' => fake()->paragraphs(2, true),
            'description_mr' => null,
            'cover_image' => null,
            'gallery' => null,
            'year' => 2024,
            'is_sample' => false,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function sample(): static
    {
        return $this->state(fn () => ['is_sample' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}

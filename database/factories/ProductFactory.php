<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify,
            'status' => $this->faker->randomElement(['published', 'draft']),
            'imported_t' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'url' => $this->faker->url,
            'creator' => $this->faker->name,
            'created_t' => random_int(1000000000, 9999999999),
            'last_modified_t' => random_int(1000000000, 9999999999),
            'product_name' => $this->faker->word,
            'quantity' => strval($this->faker->randomDigit),
            'brands' => $this->faker->word,
            'categories' => $this->faker->word,
            'labels' => $this->faker->word,
            'cities' => $this->faker->word,
            'purchase_places' => $this->faker->word,
            'stores' => $this->faker->word,
            'ingredients_text' => $this->faker->sentence,
            'traces' => $this->faker->word,
            'serving_size' => strval($this->faker->randomFloat(2, 0, 100)),
            'serving_quantity' => $this->faker->randomNumber(2),
            'nutriscore_score' => $this->faker->numberBetween(0, 100),
            'nutriscore_grade' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'main_category' => $this->faker->word,
            'image_url' => $this->faker->imageUrl(200, 200),
        ];
    }
}

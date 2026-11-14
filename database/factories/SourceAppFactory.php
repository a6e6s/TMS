<?php

namespace Database\Factories;

use App\Models\SourceApp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SourceApp>
 */
class SourceAppFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'user_id' => User::factory()->serviceAccount(),
            'pms_project_id' => fake()->numerify('PRJ-###'),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\TicketLocale;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_name' => fake()->name(),
            'reporter_email' => fake()->safeEmail(),
            'locale' => TicketLocale::English,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'page_url' => fake()->url(),
            'attachments' => null,
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Medium,
        ];
    }
}

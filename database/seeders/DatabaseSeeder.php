<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password'],
        );

        $superAdmin = User::query()->updateOrCreate(
            ['email' => config('tms.admin.email')],
            [
                'name' => 'Super Admin',
                'password' => config('tms.admin.password'),
                'user_type' => UserType::Staff,
                'email_verified_at' => now(),
            ],
        );

        $superAdmin->syncRoles([UserRole::SuperAdmin->value]);
    }
}

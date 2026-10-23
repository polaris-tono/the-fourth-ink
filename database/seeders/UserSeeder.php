<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_USER_PASSWORD');

        if (blank($password)) {
            $this->command->error('Set SEED_USER_PASSWORD in .env first, then run: php artisan config:clear');
            return;
        }

        $accounts = [
            ['Demo Admin', 'admin@fourthink.test', Role::Admin],
            ['Demo Contributor', 'contributor@fourthink.test', Role::Contributor],
            ['Demo Reader', 'reader@fourthink.test', Role::Reader],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->forceFill([
                'name' => $name,
                'password' => $password,   // hashed automatically by the model cast
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        }
    }
}
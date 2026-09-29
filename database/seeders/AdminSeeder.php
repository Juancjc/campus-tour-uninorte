<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('campus.admin.email');
        $password = config('campus.admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL e ADMIN_PASSWORD não definidos; administrador não criado.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('campus.admin.name', 'Administrador'),
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}

<?php

namespace App\Actions;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    /** @param array{name: string, email: string, school_name: string, profession_id: int, password: string} $data */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'school_name' => $data['school_name'],
                'profession_id' => $data['profession_id'],
                'password' => $data['password'],
            ]);

            SendWelcomeEmailJob::dispatch($user->id)->afterCommit();

            return $user;
        });
    }
}

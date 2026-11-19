<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // The User model hashes the password through its "hashed" cast.
        User::updateOrCreate(
            ['email' => 'admin@markham.test'],
            ['name' => 'Administrator', 'password' => 'Admin@12345', 'is_admin' => true]
        );
    }
}

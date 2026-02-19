<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Let's make sure everyone has the same password and
        // let's hash it before the loop, or else our seeder
        // will be too slow.
        $password = Hash::make('@dGmW3b2020');

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@digima.com',
            'password' => $password,
            'crypt' => encrypt('admin@digima.com'),
        ]);

        // And now let's generate a few dozen users for our app:
        User::factory()
            ->count(10)
            ->create();
    }
}

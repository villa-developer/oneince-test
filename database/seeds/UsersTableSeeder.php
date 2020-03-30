<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Jaun Carlos',
            'email' => 'ing.carlosagaton@gmail.com',
            'username' => 'carlosagaton',
            'password' => Hash::make('secret123')
        ]);
    }
}

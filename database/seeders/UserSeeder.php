<?php

namespace Database\Seeders;

use DB;
use Schema;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        $email = 'datascribe@scio.systems';
        $password = 'scio';

        // Create the users.
        User::upsert([
            [
                'firstname' => 'Scio',
                'lastname' => 'Systems',
                'email' => $email,
                'password' => bcrypt($password),
                'identity_provider' => User::IDENTITY_PROVIDER_LOCAL,
            ],
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john@example.com',
                'password' => bcrypt($password),
                'identity_provider' => User::IDENTITY_PROVIDER_LOCAL,
            ], [
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'jane@example.com',
                'password' => bcrypt($password),
                'identity_provider' => User::IDENTITY_PROVIDER_LOCAL,
            ], [
                'firstname' => 'Bob',
                'lastname' => 'Doe',
                'email' => 'bob@example.com',
                'password' => bcrypt($password),
                'identity_provider' => User::IDENTITY_PROVIDER_LOCAL,
            ], [
                'firstname' => 'Tom',
                'lastname' => 'Doe',
                'email' => 'tom@example.com',
                'password' => bcrypt($password),
                'identity_provider' => User::IDENTITY_PROVIDER_LOCAL,
            ]
        ], ['email']);

        Schema::enableForeignKeyConstraints();
    }
}

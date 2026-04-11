<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
        ]);
    }
}

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',    'display_name' => 'Administrator'],
            ['name' => 'staff',    'display_name' => 'Event Staff'],
            ['name' => 'attendee', 'display_name' => 'Attendee'],
            ['name' => 'sponsor',  'display_name' => 'Sponsor'],
            ['name' => 'speaker',  'display_name' => 'Speaker'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}

// database/seeders/AdminSeeder.php
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        \App\Models\User::firstOrCreate(
            ['email' => 'admin@heidedal.co.za'],
            [
                'name'              => 'Admin User',
                'password'          => bcrypt('password'),
                'role_id'           => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
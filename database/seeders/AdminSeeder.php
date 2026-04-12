<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            $this->command->warn('AdminSeeder: admin role not found — run RoleSeeder first.');
            return;
        }

        User::firstOrCreate(
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

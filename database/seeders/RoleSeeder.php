<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

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

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate([
            'full_name' => 'Super Admin',
            'email' => 'superadmin@jobjet.com',
            'password' => 'password',
        ]);
        $superAdminRole = Role::findByName('super_admin');
        $superAdmin->assignRole($superAdminRole);
    }
}

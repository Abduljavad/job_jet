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
            'email' => 'superadmin@jobjet.com',
            'password' => 'password',
            'is_admin' => true,
        ]);
        $superAdmin->profile()->create([
            'full_name' => 'super_admin',
        ]);
        $superAdminRole = Role::findByName('super_admin');
        $superAdmin->assignRole($superAdminRole);
    }
}

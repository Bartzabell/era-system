<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Admin Access',
                'slug' => 'admin_access',
                'permission_for' => 'General',
                'description' => 'Full access to all system features',
            ],
            [
                'name' => 'View Dashboard',
                'slug' => 'view_dashboard',
                'permission_for' => 'Module',
                'description' => 'Can view module module',
            ],
            [
                'name' => 'Responder Access',
                'slug' => 'responder_access',
                'permission_for' => 'General',
                'description' => 'Responder Access',
            ],
            [
                'name' => 'Citizen Access',
                'slug' => 'citizen_access',
                'permission_for' => 'General',
                'description' => 'Citizen Access',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}

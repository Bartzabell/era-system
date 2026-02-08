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
                'permission_for' => 'General',
                'description' => 'Can view module module',
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

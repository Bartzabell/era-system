<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);

        $roles = [
            ['name' => 'administrator'],
            ['name' => 'user'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        $admin = User::firstOrCreate(
            ['username' => 'administrator',
             'email' => 'admin@local'],
            [
                'name' => 'Administrator',
                'role_id' => 1,
                'password' => Hash::make('password'),
            ]
        );

        $adminPermissionId = Permission::where('slug', 'admin_access')->pluck('id')->first();
        $admin->permissions()->sync([$adminPermissionId]);
    }
}

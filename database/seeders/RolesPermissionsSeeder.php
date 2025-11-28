<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Organization;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create default organization if it doesn't exist
        $organization = Organization::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Default Organization',
                'timezone' => 'UTC',
                'country' => 'US',
                'is_active' => true,
            ]
        );

        // Define all permissions
        $permissions = [
            // Administration
            'access admin',
            'view dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage organizations',
            
            // Operations
            'manage qa',
            'manage cultivation',
            'manage manufacturing',
            'manage inventory',
            'manage sales',
            'manage procurement',
            
            // View permissions
            'view users',
            'view roles',
            'view permissions',
            'view qa',
            'view cultivation',
            'view manufacturing',
            'view inventory',
            'view sales',
            'view procurement',
            
            // Create permissions
            'create users',
            'create roles',
            'create permissions',
            'create qa',
            'create cultivation',
            'create manufacturing',
            'create inventory',
            'create sales',
            'create procurement',
            
            // Edit permissions
            'edit users',
            'edit roles',
            'edit permissions',
            'edit qa',
            'edit cultivation',
            'edit manufacturing',
            'edit inventory',
            'edit sales',
            'edit procurement',
            
            // Delete permissions
            'delete users',
            'delete roles',
            'delete permissions',
            'delete qa',
            'delete cultivation',
            'delete manufacturing',
            'delete inventory',
            'delete sales',
            'delete procurement',
            
            // Approve permissions
            'approve qa',
            'approve cultivation',
            'approve manufacturing',
            'approve inventory',
            'approve sales',
            'approve procurement',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Define roles with their permissions
        $roles = [
            'Administrator' => $permissions, // All permissions
            
            'QA Manager' => [
                'access admin',
                'view dashboard',
                'manage qa',
                'view qa',
                'create qa',
                'edit qa',
                'delete qa',
                'approve qa',
                'view cultivation',
                'view manufacturing',
                'view inventory',
            ],
            
            'Cultivation Operator' => [
                'access admin',
                'view dashboard',
                'manage cultivation',
                'view cultivation',
                'create cultivation',
                'edit cultivation',
                'view qa',
            ],
            
            'Cultivation Supervisor' => [
                'access admin',
                'view dashboard',
                'manage cultivation',
                'view cultivation',
                'create cultivation',
                'edit cultivation',
                'delete cultivation',
                'approve cultivation',
                'view qa',
            ],
            
            'Manufacturing Technician' => [
                'access admin',
                'view dashboard',
                'manage manufacturing',
                'view manufacturing',
                'create manufacturing',
                'edit manufacturing',
                'view inventory',
                'view qa',
            ],
            
            'Manufacturing Manager' => [
                'access admin',
                'view dashboard',
                'manage manufacturing',
                'view manufacturing',
                'create manufacturing',
                'edit manufacturing',
                'delete manufacturing',
                'approve manufacturing',
                'view inventory',
                'view qa',
            ],
            
            'Inventory Controller' => [
                'access admin',
                'view dashboard',
                'manage inventory',
                'view inventory',
                'create inventory',
                'edit inventory',
                'view cultivation',
                'view manufacturing',
                'view sales',
            ],
            
            'Sales Executive' => [
                'access admin',
                'view dashboard',
                'manage sales',
                'view sales',
                'create sales',
                'edit sales',
                'view inventory',
            ],
            
            'Sales Manager' => [
                'access admin',
                'view dashboard',
                'manage sales',
                'view sales',
                'create sales',
                'edit sales',
                'delete sales',
                'approve sales',
                'view inventory',
            ],
            
            'Procurement Officer' => [
                'access admin',
                'view dashboard',
                'manage procurement',
                'view procurement',
                'create procurement',
                'edit procurement',
                'view inventory',
            ],
            
            'Viewer' => [
                'access admin',
                'view dashboard',
                'view users',
                'view roles',
                'view permissions',
                'view qa',
                'view cultivation',
                'view manufacturing',
                'view inventory',
                'view sales',
                'view procurement',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
            $role->syncPermissions($rolePermissions);
        }

        // Create default admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'organization_id' => $organization->id,
                'is_active' => true,
            ]
        );

        // Assign Administrator role to admin user
        if (!$admin->hasRole('Administrator')) {
            $admin->assignRole('Administrator');
        }
    }
}

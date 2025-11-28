<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure base permissions exist (adjust as you like)
        $perms = [
            'access admin',
            'view dashboard',
            'manage users',
            'manage roles',
            'manage inventory',
            'manage cultivation',
            'manage qa',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Roles
        $super = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $qa    = Role::firstOrCreate(['name' => 'QA', 'guard_name' => 'web']);

        // Map some defaults
        $super->syncPermissions(Permission::all());
        $admin->syncPermissions(['access admin','view dashboard','manage inventory','manage cultivation']);
        $qa->syncPermissions(['access admin','view dashboard','manage qa']);

        // Promote an existing user (change email if yours is different)
        $user = User::where('email', 'admin@admin.com')->first()
            ?? User::first(); // fallback: first user in DB

        if ($user) {
            $user->assignRole('Super Admin');
            // optional: allow entering /admin even if your gate checks permission
            $user->givePermissionTo('access admin');
        }
    }
}
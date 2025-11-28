<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AIPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create AI permissions
        $permissions = [
            'ai.use' => 'Access AI features',
            'ai.detect.anomaly' => 'Use AI plant anomaly detection',
            'ai.classify.plant' => 'Use AI plant classification',
            'ai.chat' => 'Use AI cultivation chatbot',
            'ai.manage' => 'Manage AI settings and knowledge base',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('AI permissions created and assigned successfully!');
    }

    /**
     * Assign AI permissions to existing roles
     */
    protected function assignPermissionsToRoles(): void
    {
        // Administrator gets all AI permissions
        $admin = Role::findByName('Administrator', 'web');
        $admin->givePermissionTo([
            'ai.use',
            'ai.detect.anomaly',
            'ai.classify.plant',
            'ai.chat',
            'ai.manage',
        ]);

        // QA Manager
        if ($qaManager = Role::where('name', 'QA Manager')->first()) {
            $qaManager->givePermissionTo([
                'ai.use',
                'ai.detect.anomaly',
                'ai.classify.plant',
                'ai.chat',
            ]);
        }

        // Cultivation Supervisor
        if ($cultivationSupervisor = Role::where('name', 'Cultivation Supervisor')->first()) {
            $cultivationSupervisor->givePermissionTo([
                'ai.use',
                'ai.detect.anomaly',
                'ai.classify.plant',
                'ai.chat',
            ]);
        }

        // Cultivation Operator
        if ($cultivationOperator = Role::where('name', 'Cultivation Operator')->first()) {
            $cultivationOperator->givePermissionTo([
                'ai.use',
                'ai.detect.anomaly',
                'ai.classify.plant',
                'ai.chat',
            ]);
        }

        // Manufacturing Manager
        if ($mfgManager = Role::where('name', 'Manufacturing Manager')->first()) {
            $mfgManager->givePermissionTo([
                'ai.use',
                'ai.chat',
            ]);
        }
    }
}

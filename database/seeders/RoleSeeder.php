<?php

/**
 * ACTO Maps - Role Seeder
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $operator = Role::create(['name' => 'operator', 'guard_name' => 'web']);
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        // Get all permissions
        $permissions = Permission::all();

        // Admin has all permissions
        $admin->syncPermissions($permissions);

        // Operator can manage layers but not users
        $operator->syncPermissions([
            'view_layer',
            'create_layer',
            'edit_layer',
            'delete_layer',
            'view_audit',
        ]);

        // Viewer can only view
        $viewer->syncPermissions([
            'view_layer',
        ]);

        $this->command->info('Roles created successfully!');
        $this->command->info('- admin: Full access');
        $this->command->info('- operator: Manage layers');
        $this->command->info('- viewer: View only');
    }
}


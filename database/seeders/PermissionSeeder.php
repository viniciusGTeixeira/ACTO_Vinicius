<?php

/**
 * ACTO Maps - Permission Seeder
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Layer permissions
            ['name' => 'view_layer', 'description' => 'View layers'],
            ['name' => 'create_layer', 'description' => 'Create new layers'],
            ['name' => 'edit_layer', 'description' => 'Edit existing layers'],
            ['name' => 'delete_layer', 'description' => 'Delete layers'],
            
            // User management permissions
            ['name' => 'view_user', 'description' => 'View users'],
            ['name' => 'create_user', 'description' => 'Create new users'],
            ['name' => 'edit_user', 'description' => 'Edit existing users'],
            ['name' => 'delete_user', 'description' => 'Delete users'],
            ['name' => 'manage_roles', 'description' => 'Assign roles to users'],
            
            // Audit permissions
            ['name' => 'view_audit', 'description' => 'View audit logs'],
            
            // System permissions
            ['name' => 'view_dashboard', 'description' => 'Access admin dashboard'],
            ['name' => 'manage_settings', 'description' => 'Manage system settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'guard_name' => 'web',
            ]);
            
            $this->command->info("Permission created: {$permission['name']}");
        }

        $this->command->info('All permissions created successfully!');
    }
}


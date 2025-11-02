<?php

/**
 * ACTO Maps - Admin User Seeder
 * 
 * @license license.txt
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@actomaps.com',
            'password' => Hash::make('Admin@123456'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('Admin user created successfully!');
        $this->command->info('========================================');
        $this->command->info('Email: admin@actomaps.com');
        $this->command->info('Password: Admin@123456');
        $this->command->info('========================================');
        $this->command->info('IMPORTANT: Change this password after first login!');
        $this->command->info('========================================');
        $this->command->info('');
    }
}


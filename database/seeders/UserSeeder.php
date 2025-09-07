<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default team first
        $defaultTeam = Team::firstOrCreate([
            'name' => 'Liberu Maintenance Team',
            'user_id' => 1,
            'personal_team' => false,
        ]);

        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@liberu.co.uk'
        ], [
            'name' => 'System Administrator',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_team_id' => $defaultTeam->id,
        ]);

        // Update team user_id if needed
        if ($defaultTeam->user_id !== $admin->id) {
            $defaultTeam->update(['user_id' => $admin->id]);
        }

        // Create maintenance manager
        User::firstOrCreate([
            'email' => 'manager@liberu.co.uk'
        ], [
            'name' => 'Maintenance Manager',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_team_id' => $defaultTeam->id,
        ]);

        // Create maintenance technicians
        $technicians = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@liberu.co.uk',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@liberu.co.uk',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Mike Wilson',
                'email' => 'mike.wilson@liberu.co.uk',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Emma Davis',
                'email' => 'emma.davis@liberu.co.uk',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($technicians as $technicianData) {
            User::firstOrCreate([
                'email' => $technicianData['email']
            ], array_merge($technicianData, [
                'email_verified_at' => now(),
                'current_team_id' => $defaultTeam->id,
            ]));
        }

        // Create facility managers
        $facilityManagers = [
            [
                'name' => 'Robert Brown',
                'email' => 'robert.brown@liberu.co.uk',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@liberu.co.uk',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($facilityManagers as $managerData) {
            User::firstOrCreate([
                'email' => $managerData['email']
            ], array_merge($managerData, [
                'email_verified_at' => now(),
                'current_team_id' => $defaultTeam->id,
            ]));
        }

        // Create demo users with different roles
        $demoUsers = [
            [
                'name' => 'Demo Tenant',
                'email' => 'tenant@demo.com',
                'password' => Hash::make('demo123'),
            ],
            [
                'name' => 'Demo Contractor',
                'email' => 'contractor@demo.com',
                'password' => Hash::make('demo123'),
            ],
            [
                'name' => 'Demo Landlord',
                'email' => 'landlord@demo.com',
                'password' => Hash::make('demo123'),
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::firstOrCreate([
                'email' => $userData['email']
            ], array_merge($userData, [
                'email_verified_at' => now(),
                'current_team_id' => $defaultTeam->id,
            ]));
        }

        $this->command->info('Users seeded successfully!');
    }
}
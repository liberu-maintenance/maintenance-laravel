<?php

namespace Database\Seeders;

use App\Models\MaintenanceSchedule;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Checklist;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaintenanceScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = Equipment::all();
        $users = User::all();
        $checklists = Checklist::all();
        $defaultTeam = Team::where('name', 'Liberu Maintenance Team')->first();

        if ($equipment->isEmpty()) {
            $this->command->warn('No equipment found. Please run EquipmentSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $schedules = [
            // Critical Equipment - Monthly Schedules
            [
                'name' => 'HVAC Chiller Monthly Inspection',
                'description' => 'Comprehensive monthly inspection of the main chiller unit including refrigerant levels, electrical connections, and performance monitoring.',
                'frequency_type' => 'monthly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDays(15),
                'last_completed_date' => Carbon::now()->subMonth(),
                'estimated_duration' => 180, // 3 hours
                'priority' => 'critical',
                'status' => 'active',
                'instructions' => 'Check refrigerant levels, inspect electrical connections, verify temperature and pressure readings, clean condenser coils, test safety controls.',
            ],
            [
                'name' => 'Emergency Generator Load Test',
                'description' => 'Monthly load testing of emergency generator to ensure proper operation during power outages.',
                'frequency_type' => 'monthly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDays(7),
                'last_completed_date' => Carbon::now()->subMonth(),
                'estimated_duration' => 120, // 2 hours
                'priority' => 'critical',
                'status' => 'active',
                'instructions' => 'Start generator, apply 50% load for 30 minutes, check oil levels, inspect battery, verify automatic transfer switch operation.',
            ],
            [
                'name' => 'Fire Alarm System Test',
                'description' => 'Monthly testing of fire alarm system components and emergency notification systems.',
                'frequency_type' => 'monthly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDays(10),
                'last_completed_date' => Carbon::now()->subMonth(),
                'estimated_duration' => 240, // 4 hours
                'priority' => 'critical',
                'status' => 'active',
                'instructions' => 'Test all smoke detectors, verify alarm panel operation, check emergency lighting, test notification devices.',
            ],

            // Weekly Schedules
            [
                'name' => 'Sprinkler System Pump Test',
                'description' => 'Weekly testing of fire sprinkler system pump to ensure proper pressure and operation.',
                'frequency_type' => 'weekly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDays(3),
                'last_completed_date' => Carbon::now()->subWeek(),
                'estimated_duration' => 30,
                'priority' => 'high',
                'status' => 'active',
                'instructions' => 'Start pump manually, verify pressure readings, check for leaks, test automatic start function.',
            ],
            [
                'name' => 'Elevator Safety Inspection',
                'description' => 'Weekly safety inspection of passenger elevators including door operation and emergency systems.',
                'frequency_type' => 'weekly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDays(5),
                'last_completed_date' => Carbon::now()->subWeek(),
                'estimated_duration' => 90,
                'priority' => 'high',
                'status' => 'active',
                'instructions' => 'Test door sensors, verify emergency phone, check floor leveling, inspect cables and pulleys.',
            ],

            // Quarterly Schedules
            [
                'name' => 'HVAC Filter Replacement',
                'description' => 'Quarterly replacement of HVAC system filters throughout all buildings.',
                'frequency_type' => 'monthly',
                'frequency_value' => 3,
                'next_due_date' => Carbon::now()->addMonths(2),
                'last_completed_date' => Carbon::now()->subMonth(),
                'estimated_duration' => 480, // 8 hours
                'priority' => 'medium',
                'status' => 'active',
                'instructions' => 'Replace all air filters, inspect ductwork, clean vents, check airflow measurements.',
            ],
            [
                'name' => 'UPS Battery Test',
                'description' => 'Quarterly battery testing and maintenance for UPS systems.',
                'frequency_type' => 'monthly',
                'frequency_value' => 3,
                'next_due_date' => Carbon::now()->addMonths(1),
                'last_completed_date' => Carbon::now()->subMonths(2),
                'estimated_duration' => 120,
                'priority' => 'high',
                'status' => 'active',
                'instructions' => 'Test battery capacity, check connections, verify runtime, inspect for corrosion.',
            ],

            // Annual Schedules
            [
                'name' => 'Boiler Annual Inspection',
                'description' => 'Annual comprehensive inspection and maintenance of boiler system.',
                'frequency_type' => 'yearly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addMonths(8),
                'last_completed_date' => Carbon::now()->subMonths(4),
                'estimated_duration' => 600, // 10 hours
                'priority' => 'high',
                'status' => 'active',
                'instructions' => 'Complete combustion analysis, inspect heat exchanger, test safety controls, clean burner assembly.',
            ],
            [
                'name' => 'Elevator Annual Certification',
                'description' => 'Annual elevator inspection and certification by licensed inspector.',
                'frequency_type' => 'yearly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addMonths(6),
                'last_completed_date' => Carbon::now()->subMonths(6),
                'estimated_duration' => 360, // 6 hours
                'priority' => 'critical',
                'status' => 'active',
                'instructions' => 'Complete state-required inspection, test all safety systems, verify code compliance.',
            ],

            // Daily Schedules
            [
                'name' => 'Kitchen Equipment Daily Check',
                'description' => 'Daily inspection of commercial kitchen equipment for proper operation.',
                'frequency_type' => 'daily',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDay(),
                'last_completed_date' => Carbon::now(),
                'estimated_duration' => 45,
                'priority' => 'medium',
                'status' => 'active',
                'instructions' => 'Check refrigerator temperatures, test dishwasher operation, inspect for leaks.',
            ],

            // Completed Schedule Example
            [
                'name' => 'Parking Gate Lubrication',
                'description' => 'Monthly lubrication and adjustment of parking garage barrier gate.',
                'frequency_type' => 'monthly',
                'frequency_value' => 1,
                'next_due_date' => Carbon::now()->addDays(20),
                'last_completed_date' => Carbon::now()->subDays(10),
                'estimated_duration' => 60,
                'priority' => 'low',
                'status' => 'completed',
                'instructions' => 'Lubricate all moving parts, adjust gate timing, test remote controls.',
            ],
        ];

        foreach ($schedules as $scheduleData) {
            // Assign random equipment
            $scheduleData['equipment_id'] = $equipment->random()->id;

            // Assign random user
            $scheduleData['assigned_to'] = $users->random()->id;

            // Assign random checklist if available
            if ($checklists->isNotEmpty() && rand(0, 1)) {
                $scheduleData['checklist_id'] = $checklists->random()->id;
            }

            // Assign default team
            $scheduleData['team_id'] = $defaultTeam?->id;

            MaintenanceSchedule::create($scheduleData);
        }

        $this->command->info('Maintenance schedules seeded successfully!');
    }
}
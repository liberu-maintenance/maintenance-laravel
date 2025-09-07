<?php

namespace Database\Seeders;

use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $equipment = Equipment::all();
        $defaultTeam = Team::where('name', 'Liberu Maintenance Team')->first();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $workOrders = [
            // Emergency/Critical Work Orders
            [
                'title' => 'HVAC System Complete Failure - Building A',
                'description' => 'The main HVAC system in Building A has completely failed. No heating or cooling throughout the building. Immediate attention required as it affects all tenants.',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'location' => 'Building A - Mechanical Room',
                'guest_name' => 'Emergency Facilities',
                'guest_email' => 'emergency@building-a.com',
                'guest_phone' => '+44 20 1234 5678',
                'notes' => 'Equipment: Carrier 30XA Chiller Unit - Serial: CU2023001',
            ],
            [
                'title' => 'Water Leak in Server Room',
                'description' => 'Major water leak detected in the server room ceiling. Water is dripping onto critical IT equipment. Emergency response needed to prevent equipment damage.',
                'priority' => 'urgent',
                'status' => 'pending',
                'location' => 'Building B - Floor 3 - Server Room',
                'guest_name' => 'IT Manager',
                'guest_email' => 'it.manager@company.com',
                'guest_phone' => '+44 20 1234 5679',
                'notes' => 'Equipment: Ceiling mounted sprinkler system',
            ],

            // High Priority Work Orders
            [
                'title' => 'Elevator Out of Service - Main Building',
                'description' => 'The main passenger elevator is stuck between floors 5 and 6. No passengers trapped, but elevator is completely non-functional. Backup elevator is operational.',
                'priority' => 'high',
                'status' => 'approved',
                'location' => 'Main Building - Elevator Bank A',
                'guest_name' => 'Building Security',
                'guest_email' => 'security@mainbuilding.com',
                'guest_phone' => '+44 20 1234 5680',
                'notes' => 'Equipment: Otis Gen2 Elevator - Unit A1',
            ],
            [
                'title' => 'Fire Alarm System Malfunction',
                'description' => 'Fire alarm system is triggering false alarms every 30 minutes. System needs immediate inspection and repair to ensure building safety compliance.',
                'priority' => 'high',
                'status' => 'in_progress',
                'location' => 'Building C - Fire Control Panel',
                'guest_name' => 'Safety Officer',
                'guest_email' => 'safety@buildingc.com',
                'guest_phone' => '+44 20 1234 5681',
                'notes' => 'Equipment: Honeywell Fire Alarm Panel - Model FA-2000',
            ],

            // Medium Priority Work Orders
            [
                'title' => 'Office Lighting Flickering - Floor 2',
                'description' => 'Multiple fluorescent lights on floor 2 are flickering intermittently. Affecting productivity in the accounting department. Needs electrical inspection.',
                'priority' => 'medium',
                'status' => 'pending',
                'location' => 'Office Building - Floor 2 - Accounting Dept',
                'guest_name' => 'Department Manager',
                'guest_email' => 'accounting.manager@office.com',
                'guest_phone' => '+44 20 1234 5682',
                'notes' => 'Equipment: Philips T8 Fluorescent Fixtures',
            ],
            [
                'title' => 'Parking Garage Gate Malfunction',
                'description' => 'The automated parking garage gate is not responding to key cards. Tenants are having difficulty accessing the parking area.',
                'priority' => 'medium',
                'status' => 'approved',
                'location' => 'Underground Parking - Main Entrance',
                'guest_name' => 'Parking Attendant',
                'guest_email' => 'parking@building.com',
                'guest_phone' => '+44 20 1234 5683',
                'notes' => 'Equipment: FAAC Barrier Gate System - Model 615',
            ],
            [
                'title' => 'Conference Room AV Equipment Issues',
                'description' => 'The projector in Conference Room A is not displaying properly. Screen shows distorted colors and occasional blackouts during presentations.',
                'priority' => 'medium',
                'status' => 'completed',
                'location' => 'Office Building - Floor 4 - Conference Room A',
                'guest_name' => 'Executive Assistant',
                'guest_email' => 'exec.assistant@office.com',
                'guest_phone' => '+44 20 1234 5684',
                'notes' => 'Equipment: Epson PowerLite Projector - Model 5050UB',
            ],

            // Low Priority Work Orders
            [
                'title' => 'Landscaping Maintenance Required',
                'description' => 'The front garden area needs general maintenance. Bushes need trimming, weeds need removal, and flower beds need fresh mulch.',
                'priority' => 'low',
                'status' => 'pending',
                'location' => 'Building Exterior - Front Garden',
                'guest_name' => 'Property Manager',
                'guest_email' => 'property.manager@building.com',
                'guest_phone' => '+44 20 1234 5685',
                'notes' => 'Equipment: Garden maintenance tools required',
            ],
            [
                'title' => 'Office Paint Touch-ups Needed',
                'description' => 'Several offices on floor 3 have minor scuff marks and nail holes that need paint touch-ups. Cosmetic maintenance for professional appearance.',
                'priority' => 'low',
                'status' => 'approved',
                'location' => 'Office Building - Floor 3 - Multiple Offices',
                'guest_name' => 'Office Manager',
                'guest_email' => 'office.manager@building.com',
                'guest_phone' => '+44 20 1234 5686',
                'notes' => 'Equipment: Paint supplies and brushes',
            ],
            [
                'title' => 'Replace Air Fresheners in Restrooms',
                'description' => 'All restroom air fresheners on floors 1-5 need replacement. Current units are empty and need new fragrance cartridges.',
                'priority' => 'low',
                'status' => 'completed',
                'location' => 'Office Building - All Floors - Restrooms',
                'guest_name' => 'Cleaning Supervisor',
                'guest_email' => 'cleaning@building.com',
                'guest_phone' => '+44 20 1234 5687',
                'notes' => 'Equipment: Automatic air freshener dispensers',
            ],

            // Guest Submitted Work Orders
            [
                'title' => 'Broken Window Blind in Office 301',
                'description' => 'The window blind in office 301 is stuck and cannot be adjusted. The cord mechanism appears to be broken.',
                'priority' => 'low',
                'status' => 'pending',
                'location' => 'Office Building - Floor 3 - Office 301',
                'guest_name' => 'Jane Smith',
                'guest_email' => 'jane.smith@tenant.com',
                'guest_phone' => '+44 20 1234 5688',
                'notes' => 'Equipment: Vertical window blinds',
            ],
            [
                'title' => 'Noisy Ventilation Fan in Kitchen',
                'description' => 'The exhaust fan in the staff kitchen is making loud grinding noises. It still works but the noise is disruptive.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'location' => 'Office Building - Floor 2 - Staff Kitchen',
                'guest_name' => 'Kitchen Staff',
                'guest_email' => 'kitchen@building.com',
                'guest_phone' => '+44 20 1234 5689',
                'notes' => 'Equipment: Broan Kitchen Exhaust Fan - 6 inch',
            ],
        ];

        foreach ($workOrders as $workOrderData) {
            // Assign random reviewer for some work orders
            if (in_array($workOrderData['status'], ['approved', 'in_progress', 'completed'])) {
                $workOrderData['reviewed_by'] = $users->random()->id;
                $workOrderData['reviewed_at'] = Carbon::now()->subHours(rand(1, 48));
            }

            // Assign equipment if available
            if ($equipment->isNotEmpty() && rand(0, 1)) {
                $workOrderData['equipment_id'] = $equipment->random()->id;
            }

            // Set submitted_at timestamp
            $workOrderData['submitted_at'] = Carbon::now()->subHours(rand(1, 168)); // Random time within last week

            // Assign default team
            $workOrderData['team_id'] = $defaultTeam?->id;

            WorkOrder::create($workOrderData);
        }

        $this->command->info('Work orders seeded successfully!');
    }
}
<?php

namespace Database\Seeders;

use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Equipment;
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

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $workOrders = [
            // Emergency/Critical Work Orders
            [
                'title' => 'HVAC System Complete Failure - Building A',
                'description' => 'The main HVAC system in Building A has completely failed. No heating or cooling throughout the building. Immediate attention required as it affects all tenants.',
                'priority' => 'critical',
                'status' => 'in_progress',
                'location' => 'Building A - Mechanical Room',
                'contact_name' => 'Emergency Facilities',
                'contact_email' => 'emergency@building-a.com',
                'contact_phone' => '+44 20 1234 5678',
                'equipment_details' => 'Carrier 30XA Chiller Unit - Serial: CU2023001',
                'preferred_date' => Carbon::now()->addHours(2),
                'submitted_by_guest' => false,
                'company_id' => 1,
            ],
            [
                'title' => 'Water Leak in Server Room',
                'description' => 'Major water leak detected in the server room ceiling. Water is dripping onto critical IT equipment. Emergency response needed to prevent equipment damage.',
                'priority' => 'critical',
                'status' => 'pending',
                'location' => 'Building B - Floor 3 - Server Room',
                'contact_name' => 'IT Manager',
                'contact_email' => 'it.manager@company.com',
                'contact_phone' => '+44 20 1234 5679',
                'equipment_details' => 'Ceiling mounted sprinkler system',
                'preferred_date' => Carbon::now()->addMinutes(30),
                'submitted_by_guest' => false,
                'company_id' => 1,
            ],

            // High Priority Work Orders
            [
                'title' => 'Elevator Out of Service - Main Building',
                'description' => 'The main passenger elevator is stuck between floors 5 and 6. No passengers trapped, but elevator is completely non-functional. Backup elevator is operational.',
                'priority' => 'high',
                'status' => 'approved',
                'location' => 'Main Building - Elevator Bank A',
                'contact_name' => 'Building Security',
                'contact_email' => 'security@mainbuilding.com',
                'contact_phone' => '+44 20 1234 5680',
                'equipment_details' => 'Otis Gen2 Elevator - Unit A1',
                'preferred_date' => Carbon::now()->addHours(4),
                'submitted_by_guest' => false,
                'company_id' => 1,
            ],
            [
                'title' => 'Fire Alarm System Malfunction',
                'description' => 'Fire alarm system is triggering false alarms every 30 minutes. System needs immediate inspection and repair to ensure building safety compliance.',
                'priority' => 'high',
                'status' => 'in_progress',
                'location' => 'Building C - Fire Control Panel',
                'contact_name' => 'Safety Officer',
                'contact_email' => 'safety@buildingc.com',
                'contact_phone' => '+44 20 1234 5681',
                'equipment_details' => 'Honeywell Fire Alarm Panel - Model FA-2000',
                'preferred_date' => Carbon::now()->addHours(6),
                'submitted_by_guest' => false,
                'company_id' => 1,
            ],

            // Medium Priority Work Orders
            [
                'title' => 'Office Lighting Flickering - Floor 2',
                'description' => 'Multiple fluorescent lights on floor 2 are flickering intermittently. Affecting productivity in the accounting department. Needs electrical inspection.',
                'priority' => 'medium',
                'status' => 'pending',
                'location' => 'Office Building - Floor 2 - Accounting Dept',
                'contact_name' => 'Department Manager',
                'contact_email' => 'accounting.manager@office.com',
                'contact_phone' => '+44 20 1234 5682',
                'equipment_details' => 'Philips T8 Fluorescent Fixtures',
                'preferred_date' => Carbon::now()->addDays(2),
                'submitted_by_guest' => true,
                'company_id' => 1,
            ],
            [
                'title' => 'Parking Garage Gate Malfunction',
                'description' => 'The automated parking garage gate is not responding to key cards. Tenants are having difficulty accessing the parking area.',
                'priority' => 'medium',
                'status' => 'approved',
                'location' => 'Underground Parking - Main Entrance',
                'contact_name' => 'Parking Attendant',
                'contact_email' => 'parking@building.com',
                'contact_phone' => '+44 20 1234 5683',
                'equipment_details' => 'FAAC Barrier Gate System - Model 615',
                'preferred_date' => Carbon::now()->addDays(1),
                'submitted_by_guest' => true,
                'company_id' => 1,
            ],
            [
                'title' => 'Conference Room AV Equipment Issues',
                'description' => 'The projector in Conference Room A is not displaying properly. Screen shows distorted colors and occasional blackouts during presentations.',
                'priority' => 'medium',
                'status' => 'completed',
                'location' => 'Office Building - Floor 4 - Conference Room A',
                'contact_name' => 'Executive Assistant',
                'contact_email' => 'exec.assistant@office.com',
                'contact_phone' => '+44 20 1234 5684',
                'equipment_details' => 'Epson PowerLite Projector - Model 5050UB',
                'preferred_date' => Carbon::now()->subDays(3),
                'submitted_by_guest' => false,
                'company_id' => 1,
            ],

            // Low Priority Work Orders
            [
                'title' => 'Landscaping Maintenance Required',
                'description' => 'The front garden area needs general maintenance. Bushes need trimming, weeds need removal, and flower beds need fresh mulch.',
                'priority' => 'low',
                'status' => 'pending',
                'location' => 'Building Exterior - Front Garden',
                'contact_name' => 'Property Manager',
                'contact_email' => 'property.manager@building.com',
                'contact_phone' => '+44 20 1234 5685',
                'equipment_details' => 'Garden maintenance tools required',
                'preferred_date' => Carbon::now()->addWeek(),
                'submitted_by_guest' => true,
                'company_id' => 1,
            ],
            [
                'title' => 'Office Paint Touch-ups Needed',
                'description' => 'Several offices on floor 3 have minor scuff marks and nail holes that need paint touch-ups. Cosmetic maintenance for professional appearance.',
                'priority' => 'low',
                'status' => 'approved',
                'location' => 'Office Building - Floor 3 - Multiple Offices',
                'contact_name' => 'Office Manager',
                'contact_email' => 'office.manager@building.com',
                'contact_phone' => '+44 20 1234 5686',
                'equipment_details' => 'Paint supplies and brushes',
                'preferred_date' => Carbon::now()->addDays(5),
                'submitted_by_guest' => false,
                'company_id' => 1,
            ],
            [
                'title' => 'Replace Air Fresheners in Restrooms',
                'description' => 'All restroom air fresheners on floors 1-5 need replacement. Current units are empty and need new fragrance cartridges.',
                'priority' => 'low',
                'status' => 'completed',
                'location' => 'Office Building - All Floors - Restrooms',
                'contact_name' => 'Cleaning Supervisor',
                'contact_email' => 'cleaning@building.com',
                'contact_phone' => '+44 20 1234 5687',
                'equipment_details' => 'Automatic air freshener dispensers',
                'preferred_date' => Carbon::now()->subDays(1),
                'submitted_by_guest' => true,
                'company_id' => 1,
            ],

            // Guest Submitted Work Orders
            [
                'title' => 'Broken Window Blind in Office 301',
                'description' => 'The window blind in office 301 is stuck and cannot be adjusted. The cord mechanism appears to be broken.',
                'priority' => 'low',
                'status' => 'pending',
                'location' => 'Office Building - Floor 3 - Office 301',
                'contact_name' => 'Jane Smith',
                'contact_email' => 'jane.smith@tenant.com',
                'contact_phone' => '+44 20 1234 5688',
                'equipment_details' => 'Vertical window blinds',
                'preferred_date' => Carbon::now()->addDays(3),
                'submitted_by_guest' => true,
                'company_id' => 1,
            ],
            [
                'title' => 'Noisy Ventilation Fan in Kitchen',
                'description' => 'The exhaust fan in the staff kitchen is making loud grinding noises. It still works but the noise is disruptive.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'location' => 'Office Building - Floor 2 - Staff Kitchen',
                'contact_name' => 'Kitchen Staff',
                'contact_email' => 'kitchen@building.com',
                'contact_phone' => '+44 20 1234 5689',
                'equipment_details' => 'Broan Kitchen Exhaust Fan - 6 inch',
                'preferred_date' => Carbon::now()->addDays(2),
                'submitted_by_guest' => true,
                'company_id' => 1,
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

            WorkOrder::create($workOrderData);
        }

        $this->command->info('Work orders seeded successfully!');
    }
}
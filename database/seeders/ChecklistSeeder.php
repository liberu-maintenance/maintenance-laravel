<?php

namespace Database\Seeders;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = Equipment::all();
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $checklists = [
            [
                'name' => 'HVAC Monthly Inspection Checklist',
                'description' => 'Comprehensive monthly inspection checklist for HVAC systems',
                'category' => 'HVAC',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Check refrigerant levels and pressure readings',
                    'Inspect electrical connections and tighten if necessary',
                    'Clean or replace air filters',
                    'Check condenser and evaporator coils for cleanliness',
                    'Test thermostat operation and calibration',
                    'Inspect ductwork for leaks or damage',
                    'Verify proper airflow and temperature differential',
                    'Check belt tension and condition',
                    'Lubricate motors and bearings as needed',
                    'Test safety controls and emergency shutoffs',
                    'Record operating temperatures and pressures',
                    'Check for unusual noises or vibrations',
                ]
            ],
            [
                'name' => 'Generator Load Test Checklist',
                'description' => 'Monthly load testing procedure for emergency generators',
                'category' => 'Electrical',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Check engine oil level and condition',
                    'Inspect coolant level and condition',
                    'Test battery voltage and specific gravity',
                    'Check fuel level and quality',
                    'Inspect air filter condition',
                    'Start generator and allow warm-up period',
                    'Apply 25% load for 15 minutes',
                    'Apply 50% load for 15 minutes',
                    'Apply 75% load for 15 minutes',
                    'Monitor voltage, frequency, and temperature',
                    'Test automatic transfer switch operation',
                    'Check for leaks (oil, coolant, fuel)',
                    'Record all meter readings',
                    'Return to standby mode and verify proper shutdown',
                ]
            ],
            [
                'name' => 'Fire Alarm System Test Checklist',
                'description' => 'Monthly fire alarm system testing and inspection',
                'category' => 'Fire Safety',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Test fire alarm control panel operation',
                    'Check all indicator lights and displays',
                    'Test smoke detectors in each zone',
                    'Test heat detectors in each zone',
                    'Verify manual pull stations operation',
                    'Test audible alarm devices (horns/speakers)',
                    'Test visual alarm devices (strobes)',
                    'Check emergency lighting operation',
                    'Test communication with monitoring company',
                    'Verify proper zone identification',
                    'Test silence and reset functions',
                    'Document any deficiencies found',
                    'Update test log and maintenance records',
                ]
            ],
            [
                'name' => 'Elevator Safety Inspection Checklist',
                'description' => 'Weekly elevator safety inspection and testing',
                'category' => 'Transportation',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Test door operation and safety edges',
                    'Check floor leveling accuracy',
                    'Test emergency phone operation',
                    'Verify emergency lighting function',
                    'Test alarm bell operation',
                    'Check car and hall buttons operation',
                    'Inspect door tracks and guides',
                    'Test door reopening device',
                    'Check car interior condition',
                    'Verify proper floor indicators',
                    'Test emergency stop button',
                    'Check for unusual noises or vibrations',
                    'Inspect cables and connections (visual)',
                    'Test interlock systems',
                ]
            ],
            [
                'name' => 'Kitchen Equipment Daily Checklist',
                'description' => 'Daily inspection checklist for commercial kitchen equipment',
                'category' => 'Kitchen',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Check refrigerator/freezer temperatures',
                    'Test dishwasher operation and temperature',
                    'Inspect for water leaks around equipment',
                    'Check gas connections for leaks',
                    'Test exhaust fan operation',
                    'Verify proper lighting in all areas',
                    'Check floor drains for proper drainage',
                    'Inspect electrical cords and connections',
                    'Test GFCI outlets',
                    'Check fire suppression system indicators',
                    'Verify proper ventilation',
                    'Clean and sanitize all surfaces',
                ]
            ],
            [
                'name' => 'UPS System Quarterly Checklist',
                'description' => 'Quarterly maintenance checklist for UPS systems',
                'category' => 'Electrical',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Test battery capacity and runtime',
                    'Check battery voltage and connections',
                    'Inspect for battery corrosion or swelling',
                    'Test automatic transfer to battery power',
                    'Test automatic return to utility power',
                    'Check input and output voltage readings',
                    'Verify proper cooling fan operation',
                    'Test alarm functions and indicators',
                    'Check event log for any errors',
                    'Verify proper grounding connections',
                    'Test bypass operation if equipped',
                    'Update firmware if necessary',
                    'Clean unit exterior and ventilation areas',
                ]
            ],
            [
                'name' => 'Boiler Annual Inspection Checklist',
                'description' => 'Annual comprehensive boiler inspection and maintenance',
                'category' => 'HVAC',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Perform complete combustion analysis',
                    'Inspect heat exchanger for cracks or corrosion',
                    'Test all safety controls and limits',
                    'Check gas valve operation and sealing',
                    'Inspect and clean burner assembly',
                    'Test ignition system operation',
                    'Check flue gas venting system',
                    'Inspect water level controls',
                    'Test pressure relief valve operation',
                    'Check circulation pump operation',
                    'Inspect all piping and connections',
                    'Test low water cutoff operation',
                    'Verify proper water treatment',
                    'Update maintenance records and tags',
                ]
            ],
            [
                'name' => 'Access Control System Checklist',
                'description' => 'Monthly access control system inspection',
                'category' => 'Security',
                'is_template' => true,
                'status' => 'active',
                'items' => [
                    'Test card readers at all entry points',
                    'Verify proper door lock operation',
                    'Check door position sensors',
                    'Test emergency unlock functions',
                    'Verify proper access permissions',
                    'Check system event logs',
                    'Test backup power operation',
                    'Verify network connectivity',
                    'Check door closer operation',
                    'Test intercom systems if equipped',
                    'Verify proper lighting at entry points',
                    'Update user access as needed',
                ]
            ],
        ];

        foreach ($checklists as $checklistData) {
            $items = $checklistData['items'];
            unset($checklistData['items']);

            // Assign random equipment if available
            if ($equipment->isNotEmpty() && rand(0, 1)) {
                $checklistData['equipment_id'] = $equipment->random()->id;
            }

            // Assign random creator
            $checklistData['created_by'] = $users->random()->id;

            $checklist = Checklist::create($checklistData);

            // Create checklist items
            foreach ($items as $index => $itemDescription) {
                ChecklistItem::create([
                    'checklist_id' => $checklist->id,
                    'title' => $itemDescription,
                    'description' => $itemDescription,
                    'order' => $index + 1,
                    'required' => true,
                    'type' => 'checkbox',
                ]);
            }
        }

        $this->command->info('Checklists and checklist items seeded successfully!');
    }
}
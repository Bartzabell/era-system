<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\Emergency;
use App\Models\Employee;
use App\Models\Hotline;
use App\Models\Incident;
use App\Models\Permission;
use App\Models\Responder;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            SiteLocationSeeder::class,
            IncidentReportImportSeeder::class,
        ]);

        $roles = [
            ['role_name' => 'administrator'],
            ['role_name' => 'citizen'],
            ['role_name' => 'responder'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }

        $barangays = [
            ['barangay_name' => 'Aldiano Olaes', 'landmark' => ''],
            ['barangay_name' => 'Barangay 1 Poblacion', 'landmark' => 'GMA Public Market'],
            ['barangay_name' => 'Barangay 2 Poblacion', 'landmark' => 'GMA Town Hall / Municipal Hall'],
            ['barangay_name' => 'Barangay 3 Poblacion', 'landmark' => 'GMA Town Hall / Municipal Hall'],
            ['barangay_name' => 'Barangay 4 Poblacion', 'landmark' => 'GMA Public Market'],
            ['barangay_name' => 'Barangay 5 Poblacion', 'landmark' => 'GMA Public Market'],
            ['barangay_name' => 'Benjamin Tirona', 'landmark' => ''],
            ['barangay_name' => 'Bernardo Pulido', 'landmark' => ''],
            ['barangay_name' => 'Epifanio Malia', 'landmark' => ''],
            ['barangay_name' => 'Francisco De Castro', 'landmark' => ''],
            ['barangay_name' => 'Francisco Reyes', 'landmark' => ''],
            ['barangay_name' => 'Fiorello Calimag', 'landmark' => 'GMA Public Cemetery'],
            ['barangay_name' => 'Gavino Maderan', 'landmark' => ''],
            ['barangay_name' => 'Gregoria De Jesus', 'landmark' => ''],
            ['barangay_name' => 'Inocencio Salud', 'landmark' => ''],
            ['barangay_name' => 'Jacinto Lumbreras', 'landmark' => ''],
            ['barangay_name' => 'Kapitan Kua', 'landmark' => ''],
            ['barangay_name' => 'Koronel Jose P. Elises', 'landmark' => ''],
            ['barangay_name' => 'Macario Dacon', 'landmark' => ''],
            ['barangay_name' => 'Marcelino Memije', 'landmark' => ''],
            ['barangay_name' => 'Nicolasa Virata', 'landmark' => ''],
            ['barangay_name' => 'Pantaleon Granados', 'landmark' => ''],
            ['barangay_name' => 'Ramon Cruz Sr.', 'landmark' => ''],
            ['barangay_name' => 'San Gabriel', 'landmark' => 'The Church of Jesus Christ of Latter-day Saints'],
            ['barangay_name' => 'San Jose', 'landmark' => 'St. Joseph Parish Church'],
            ['barangay_name' => 'Severino De Las Alas', 'landmark' => ''],
            ['barangay_name' => 'Tiniente Tiago', 'landmark' => ''],
        ];

        foreach ($barangays as $barangay) {
            Barangay::firstOrCreate(
                ['barangay_name' => $barangay['barangay_name']],
                $barangay
            );
        }

        $hotlines = [
            [
                'hotline_name' => 'National Emergency Hotline',
                'hotline_no' => '911',
                'description' => 'National emergency response hotline for police, fire, and medical assistance.'
            ],
            [
                'hotline_name' => 'GMA Municipal Police Station',
                'hotline_no' => '(046) 489-1234',
                'description' => 'Philippine National Police – General Mariano Alvarez.'
            ],
            [
                'hotline_name' => 'GMA Bureau of Fire Protection',
                'hotline_no' => '(046) 489-5678',
                'description' => 'Fire emergencies and rescue services in GMA.'
            ],
            [
                'hotline_name' => 'GMA Municipal Disaster Risk Reduction and Management Office (MDRRMO)',
                'hotline_no' => '0998-123-4567',
                'description' => 'Disaster response, rescue, and calamity assistance.'
            ],
            [
                'hotline_name' => 'GMA Municipal Health Office',
                'hotline_no' => '(046) 489-2345',
                'description' => 'Public health services and medical assistance.'
            ],
            [
                'hotline_name' => 'GMA Municipal Hall',
                'hotline_no' => '(046) 489-3456',
                'description' => 'General inquiries and local government services.'
            ],
            [
                'hotline_name' => 'Philippine Red Cross – Cavite Chapter',
                'hotline_no' => '(046) 424-1234',
                'description' => 'Emergency medical and blood services.'
            ],
            [
                'hotline_name' => 'Meralco Customer Service',
                'hotline_no' => '16211',
                'description' => 'Electricity service concerns and power outage reports.'
            ],
            [
                'hotline_name' => 'Maynilad Customer Service',
                'hotline_no' => '1626',
                'description' => 'Water service concerns and interruption reports.'
            ],
        ];

        foreach ($hotlines as $hotline) {
            Hotline::firstOrCreate(
                ['hotline_name' => $hotline['hotline_name']],
                $hotline
            );
        }

        $emergencies = [
            [
                'emergency_name' => 'Medical Case',
                'definition' => 'Medical emergency requiring immediate attention, such as heart attack, stroke, severe bleeding, or difficulty breathing.',
            ],
            [
                'emergency_name' => 'Trauma Case',
                'definition' => 'Physical injury or trauma requiring urgent medical care, such as fractures, burns, or severe wounds.',
            ],
            [
                'emergency_name' => 'Fire Incident',
                'definition' => 'Fire emergencies including residential, commercial, or wildfires that pose a threat to life and property.',
            ],
            [
                'emergency_name' => 'Vehicle Accident',
                'definition' => 'Road traffic accidents involving injuries or fatalities, including car crashes, motorcycle accidents, and pedestrian incidents.',
            ],
            [
                'emergency_name' => 'Rescue Operation',
                'definition' => 'Situations requiring search and rescue efforts, such as people trapped in collapsed buildings, water rescues, or wilderness rescues.',
            ],
            [
                'emergency_name' => 'Crime / Security Case',
                'definition' => 'Criminal incidents or security threats that require police intervention, such as robberies, assaults, or suspicious activities.',
            ],
            [
                'emergency_name' => 'Disaster Response',
                'definition' => 'Natural or man-made disasters that require coordinated emergency response efforts, such as floods, earthquakes, typhoons, or industrial accidents.',
            ],
        ];

        foreach ($emergencies as $emergency) {
            Emergency::firstOrCreate(
                ['emergency_name' => $emergency['emergency_name']],
                $emergency
            );
        }

        $incidents = [

            // ── Medical Case ──────────────────────────────────────────
            [
                'incident_name'  => 'Difficulty Breathing / Asthma',
                'emergency_name' => 'Medical Case',
                'definition'     => 'Respiratory distress or asthma attack requiring immediate medical intervention.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 3,
            ],
            [
                'incident_name'  => 'Cardiac-Related Case',
                'emergency_name' => 'Medical Case',
                'definition'     => 'Heart attack or cardiac event requiring urgent medical response.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Stroke / Hypertension',
                'emergency_name' => 'Medical Case',
                'definition'     => 'Stroke or dangerously high blood pressure requiring immediate medical attention.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Seizure',
                'emergency_name' => 'Medical Case',
                'definition'     => 'Sudden uncontrolled electrical disturbance in the brain causing convulsions.',
                'base_severity'  => 5,
                'base_time'      => 7,
                'base_resources' => 3,
                'base_secondary' => 3,
            ],
            [
                'incident_name'  => 'Unconscious Patient',
                'emergency_name' => 'Medical Case',
                'definition'     => 'Patient is unresponsive and requires immediate assessment and stabilization.',
                'base_severity'  => 7,
                'base_time'      => 9,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Fever / Infection Case',
                'emergency_name' => 'Medical Case',
                'definition'     => 'High fever or suspected infection requiring medical evaluation.',
                'base_severity'  => 3,
                'base_time'      => 5,
                'base_resources' => 3,
                'base_secondary' => 1,
            ],
            [
                'incident_name'  => 'Diabetic Emergency',
                'emergency_name' => 'Medical Case',
                'definition'     => 'Hypoglycemia or hyperglycemia causing a medical emergency.',
                'base_severity'  => 5,
                'base_time'      => 7,
                'base_resources' => 3,
                'base_secondary' => 3,
            ],

            // ── Vehicle Accident ──────────────────────────────────────
            [
                'incident_name'  => 'Motorcycle Accident',
                'emergency_name' => 'Vehicle Accident',
                'definition'     => 'Accident involving a motorcycle resulting in injuries.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Car-to-Car Collision',
                'emergency_name' => 'Vehicle Accident',
                'definition'     => 'Collision between two or more vehicles resulting in injuries or fatalities.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Pedestrian Hit',
                'emergency_name' => 'Vehicle Accident',
                'definition'     => 'A pedestrian struck by a vehicle requiring immediate medical response.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Truck / Bus Accident',
                'emergency_name' => 'Vehicle Accident',
                'definition'     => 'Large vehicle accident with high potential for mass casualties.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Hit and Run',
                'emergency_name' => 'Vehicle Accident',
                'definition'     => 'Vehicle accident where the responsible party fled the scene.',
                'base_severity'  => 5,
                'base_time'      => 5,
                'base_resources' => 3,
                'base_secondary' => 3,
            ],
            [
                'incident_name'  => 'Road Rollover',
                'emergency_name' => 'Vehicle Accident',
                'definition'     => 'Vehicle rollover accident with high risk of serious injuries.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],

            // ── Trauma Case ───────────────────────────────────────────
            [
                'incident_name'  => 'Severe Bleeding',
                'emergency_name' => 'Trauma Case',
                'definition'     => 'Uncontrolled or life-threatening hemorrhage requiring immediate intervention.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Head Injury',
                'emergency_name' => 'Trauma Case',
                'definition'     => 'Traumatic brain injury or head wound requiring urgent medical care.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Fracture',
                'emergency_name' => 'Trauma Case',
                'definition'     => 'Broken bone requiring medical assessment and treatment.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 3,
            ],
            [
                'incident_name'  => 'Stab Wound',
                'emergency_name' => 'Trauma Case',
                'definition'     => 'Penetrating injury caused by a sharp object requiring urgent care.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Gunshot Wound',
                'emergency_name' => 'Trauma Case',
                'definition'     => 'Firearm injury with high risk of internal damage and fatality.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Fall from Height',
                'emergency_name' => 'Trauma Case',
                'definition'     => 'Injury resulting from a fall from an elevated surface.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 3,
            ],

            // ── Fire Incident ─────────────────────────────────────────
            [
                'incident_name'  => 'Residential Fire',
                'emergency_name' => 'Fire Incident',
                'definition'     => 'Fire in a home or residential building threatening lives and property.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Electrical Fire',
                'emergency_name' => 'Fire Incident',
                'definition'     => 'Fire caused by electrical faults requiring specialized response.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Grass Fire',
                'emergency_name' => 'Fire Incident',
                'definition'     => 'Wildfire or grass fire that may spread and threaten structures.',
                'base_severity'  => 5,
                'base_time'      => 5,
                'base_resources' => 5,
                'base_secondary' => 3,
            ],
            [
                'incident_name'  => 'Vehicle Fire',
                'emergency_name' => 'Fire Incident',
                'definition'     => 'A vehicle engulfed in fire, posing explosion and injury risks.',
                'base_severity'  => 5,
                'base_time'      => 5,
                'base_resources' => 5,
                'base_secondary' => 3,
            ],
            [
                'incident_name'  => 'LPG / Gas Leak',
                'emergency_name' => 'Fire Incident',
                'definition'     => 'Gas leak with high explosion and mass casualty risk.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 9,
            ],

            // ── Rescue Operation ──────────────────────────────────────
            [
                'incident_name'  => 'Drowning Incident',
                'emergency_name' => 'Rescue Operation',
                'definition'     => 'Person submerged or in danger of drowning requiring immediate water rescue.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Flood Rescue',
                'emergency_name' => 'Rescue Operation',
                'definition'     => 'Rescue of individuals trapped or displaced by floodwaters.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Entrapment',
                'emergency_name' => 'Rescue Operation',
                'definition'     => 'Person trapped in machinery, vehicle, or confined space.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Collapsed Structure',
                'emergency_name' => 'Rescue Operation',
                'definition'     => 'Building or structure collapse with potential victims trapped inside.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 9,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Missing Person Search',
                'emergency_name' => 'Rescue Operation',
                'definition'     => 'Search and rescue operation for a missing individual.',
                'base_severity'  => 5,
                'base_time'      => 5,
                'base_resources' => 5,
                'base_secondary' => 3,
            ],

            // ── Crime / Security Case ─────────────────────────────────
            [
                'incident_name'  => 'Physical Assault',
                'emergency_name' => 'Crime / Security Case',
                'definition'     => 'Physical attack on an individual requiring medical and police response.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Robbery',
                'emergency_name' => 'Crime / Security Case',
                'definition'     => 'Theft involving force or threat requiring immediate police response.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Domestic Violence',
                'emergency_name' => 'Crime / Security Case',
                'definition'     => 'Violence or abuse occurring within a household requiring intervention.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 5,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Shooting Incident',
                'emergency_name' => 'Crime / Security Case',
                'definition'     => 'Active shooter or firearm discharge incident with mass casualty risk.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Stabbing Incident',
                'emergency_name' => 'Crime / Security Case',
                'definition'     => 'Knife or sharp weapon attack requiring police and medical response.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],

            // ── Disaster Response ─────────────────────────────────────
            [
                'incident_name'  => 'Flood',
                'emergency_name' => 'Disaster Response',
                'definition'     => 'Rising floodwaters threatening lives and requiring coordinated response.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
            [
                'incident_name'  => 'Typhoon Response',
                'emergency_name' => 'Disaster Response',
                'definition'     => 'Emergency response to typhoon conditions causing widespread damage.',
                'base_severity'  => 7,
                'base_time'      => 7,
                'base_resources' => 9,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Landslide',
                'emergency_name' => 'Disaster Response',
                'definition'     => 'Mass movement of earth or debris threatening lives and structures.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 7,
                'base_secondary' => 7,
            ],
            [
                'incident_name'  => 'Earthquake Response',
                'emergency_name' => 'Disaster Response',
                'definition'     => 'Emergency response following an earthquake with potential mass casualties.',
                'base_severity'  => 9,
                'base_time'      => 9,
                'base_resources' => 9,
                'base_secondary' => 9,
            ],
            [
                'incident_name'  => 'Evacuation Assistance',
                'emergency_name' => 'Disaster Response',
                'definition'     => 'Organized evacuation of civilians from a hazardous area.',
                'base_severity'  => 5,
                'base_time'      => 5,
                'base_resources' => 7,
                'base_secondary' => 5,
            ],
        ];

        foreach ($incidents as $incident) {
            $emergency = Emergency::where('emergency_name', $incident['emergency_name'])->first();

            Incident::firstOrCreate(
                ['incident_name' => $incident['incident_name']],
                [
                    'emergency_id'   => $emergency?->id,
                    'definition'     => $incident['definition'],
                    'base_severity'  => $incident['base_severity'],
                    'base_time'      => $incident['base_time'],
                    'base_resources' => $incident['base_resources'],
                    'base_secondary' => $incident['base_secondary'],
                ]
            );
        }
        $now = Carbon::now();
        $now = now();

        $admin = User::firstOrCreate(
            ['username' => 'administrator',
             'email' => 'admin@local'],
            [
                'full_name' => 'Administrator',
                'first_name' => 'User',
                'last_name' => 'Administrator',
                'role' => 'administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
            ]
        );

        $adminPermissionId = Permission::where('slug', 'admin_access')->pluck('id')->first();
        $admin->permissions()->sync([$adminPermissionId]);

        $users = [
            [
                'username' => 'responder',
                'email' => 'responder@local',
                'full_name' => 'Responder User',
                'first_name' => 'Responder',
                'last_name' => 'User',
                'role' => 'responder',
                'mobile_no' => '09123456789',
                'birth_date' => '1999-01-01',
                'barangay_id' => null,
                'address' => '#123 Brgy Pulido GMA Cavite',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
            ],
            [
                'username' => 'citizen',
                'email' => 'citizen@local',
                'full_name' => 'Citizen User',
                'first_name' => 'Citizen',
                'last_name' => 'User',
                'role' => 'citizen',
                'mobile_no' => '09987654321',
                'birth_date' => '1995-01-01',
                'barangay_id' => 1,
                'address' => '#456 Brgy Pulido GMA Cavite',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if ($userData['role'] === 'responder') {
                $permissionId = Permission::where('slug', 'responder_access')->pluck('id')->first();
                $user->permissions()->sync([$permissionId]);

                // Create responder record
                Responder::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'is_active' => 1,
                        'created_by' => $user->id,
                    ]
                );
            } elseif ($userData['role'] === 'citizen') {
                $permissionId = Permission::where('slug', 'citizen_access')->pluck('id')->first();
                $user->permissions()->sync([$permissionId]);
            }
        }
    }
}

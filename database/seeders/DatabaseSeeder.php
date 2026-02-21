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
                'emergency_name' => 'Fire Emergency',
                'definition' => 'An uncontrolled fire causing threat to life, property, or environment.',
            ],
            [
                'emergency_name' => 'Medical Emergency',
                'definition' => 'A sudden illness or injury requiring immediate medical attention.',
            ],
            [
                'emergency_name' => 'Natural Disaster',
                'definition' => 'A catastrophic event caused by natural forces, such as earthquakes, floods, or typhoons.',
            ],
            [
                'emergency_name' => 'Accident',
                'definition' => 'Serious accident involving injuries or fatalities.',
            ],
        ];

        foreach ($emergencies as $emergency) {
            Emergency::firstOrCreate(
                ['emergency_name' => $emergency['emergency_name']],
                $emergency
            );
        }

        $incidents = [
            [
                'incident_name' => 'Road Accident',
                'definition' => 'Vehicular accident with no serious injuries.',
            ],
            [
                'incident_name' => 'Pregnancy-related Emergency',
                'definition' => 'Complications during pregnancy or childbirth requiring urgent medical care.',
            ],
            [
                'incident_name' => 'Medical Incident',
                'definition' => 'Medical issue that does not require hospitalization.',
            ],
        ];

        foreach ($incidents as $incident) {
            Incident::firstOrCreate(
                ['incident_name' => $incident['incident_name']],
                $incident
            );
        }

        $admin = User::firstOrCreate(
            ['username' => 'administrator',
             'email' => 'admin@local'],
            [
                'full_name' => 'Administrator',
                'first_name' => 'User',
                'last_name' => 'Administrator',
                'role' => 'administrator',
                'password' => Hash::make('password'),
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

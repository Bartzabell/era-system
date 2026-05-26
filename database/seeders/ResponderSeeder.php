<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Responder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResponderSeeder extends Seeder
{
    /**
     * Seed 20 responder users.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $responders = [
            [
                'username'   => 'responder_santos',
                'email'      => 'santos.miguel@local',
                'full_name'  => 'Miguel Santos',
                'first_name' => 'Miguel',
                'last_name'  => 'Santos',
                'mobile_no'  => '09171000001',
                'birth_date' => '1990-03-15',
                'address'    => '#10 Brgy San Jose GMA Cavite',
            ],
            [
                'username'   => 'responder_reyes',
                'email'      => 'reyes.ana@local',
                'full_name'  => 'Ana Reyes',
                'first_name' => 'Ana',
                'last_name'  => 'Reyes',
                'mobile_no'  => '09171000002',
                'birth_date' => '1992-07-22',
                'address'    => '#22 Brgy San Gabriel GMA Cavite',
            ],
            [
                'username'   => 'responder_dela_cruz',
                'email'      => 'delacruz.carlo@local',
                'full_name'  => 'Carlo Dela Cruz',
                'first_name' => 'Carlo',
                'last_name'  => 'Dela Cruz',
                'mobile_no'  => '09171000003',
                'birth_date' => '1988-11-05',
                'address'    => '#5 Brgy Macario Dacon GMA Cavite',
            ],
            [
                'username'   => 'responder_garcia',
                'email'      => 'garcia.liza@local',
                'full_name'  => 'Liza Garcia',
                'first_name' => 'Liza',
                'last_name'  => 'Garcia',
                'mobile_no'  => '09171000004',
                'birth_date' => '1995-01-30',
                'address'    => '#8 Brgy Epifanio Malia GMA Cavite',
            ],
            [
                'username'   => 'responder_torres',
                'email'      => 'torres.ryan@local',
                'full_name'  => 'Ryan Torres',
                'first_name' => 'Ryan',
                'last_name'  => 'Torres',
                'mobile_no'  => '09171000005',
                'birth_date' => '1991-06-18',
                'address'    => '#14 Brgy Gregoria De Jesus GMA Cavite',
            ],
            [
                'username'   => 'responder_flores',
                'email'      => 'flores.maria@local',
                'full_name'  => 'Maria Flores',
                'first_name' => 'Maria',
                'last_name'  => 'Flores',
                'mobile_no'  => '09171000006',
                'birth_date' => '1993-09-12',
                'address'    => '#3 Brgy Jacinto Lumbreras GMA Cavite',
            ],
            [
                'username'   => 'responder_mendoza',
                'email'      => 'mendoza.jose@local',
                'full_name'  => 'Jose Mendoza',
                'first_name' => 'Jose',
                'last_name'  => 'Mendoza',
                'mobile_no'  => '09171000007',
                'birth_date' => '1987-04-25',
                'address'    => '#17 Brgy Inocencio Salud GMA Cavite',
            ],
            [
                'username'   => 'responder_ramos',
                'email'      => 'ramos.kristine@local',
                'full_name'  => 'Kristine Ramos',
                'first_name' => 'Kristine',
                'last_name'  => 'Ramos',
                'mobile_no'  => '09171000008',
                'birth_date' => '1996-12-03',
                'address'    => '#9 Brgy Fiorello Calimag GMA Cavite',
            ],
            [
                'username'   => 'responder_villanueva',
                'email'      => 'villanueva.mark@local',
                'full_name'  => 'Mark Villanueva',
                'first_name' => 'Mark',
                'last_name'  => 'Villanueva',
                'mobile_no'  => '09171000009',
                'birth_date' => '1989-08-07',
                'address'    => '#21 Brgy Gavino Maderan GMA Cavite',
            ],
            [
                'username'   => 'responder_cruz',
                'email'      => 'cruz.jennifer@local',
                'full_name'  => 'Jennifer Cruz',
                'first_name' => 'Jennifer',
                'last_name'  => 'Cruz',
                'mobile_no'  => '09171000010',
                'birth_date' => '1994-02-14',
                'address'    => '#6 Brgy Ramon Cruz Sr. GMA Cavite',
            ],
            [
                'username'   => 'responder_navarro',
                'email'      => 'navarro.paulo@local',
                'full_name'  => 'Paulo Navarro',
                'first_name' => 'Paulo',
                'last_name'  => 'Navarro',
                'mobile_no'  => '09171000011',
                'birth_date' => '1990-10-20',
                'address'    => '#12 Brgy Pantaleon Granados GMA Cavite',
            ],
            [
                'username'   => 'responder_aguilar',
                'email'      => 'aguilar.sarah@local',
                'full_name'  => 'Sarah Aguilar',
                'first_name' => 'Sarah',
                'last_name'  => 'Aguilar',
                'mobile_no'  => '09171000012',
                'birth_date' => '1997-05-09',
                'address'    => '#4 Brgy Marcelino Memije GMA Cavite',
            ],
            [
                'username'   => 'responder_aquino',
                'email'      => 'aquino.dennis@local',
                'full_name'  => 'Dennis Aquino',
                'first_name' => 'Dennis',
                'last_name'  => 'Aquino',
                'mobile_no'  => '09171000013',
                'birth_date' => '1986-07-31',
                'address'    => '#18 Brgy Nicolasa Virata GMA Cavite',
            ],
            [
                'username'   => 'responder_bautista',
                'email'      => 'bautista.grace@local',
                'full_name'  => 'Grace Bautista',
                'first_name' => 'Grace',
                'last_name'  => 'Bautista',
                'mobile_no'  => '09171000014',
                'birth_date' => '1993-03-27',
                'address'    => '#7 Brgy Koronel Jose P. Elises GMA Cavite',
            ],
            [
                'username'   => 'responder_castillo',
                'email'      => 'castillo.robin@local',
                'full_name'  => 'Robin Castillo',
                'first_name' => 'Robin',
                'last_name'  => 'Castillo',
                'mobile_no'  => '09171000015',
                'birth_date' => '1991-11-16',
                'address'    => '#11 Brgy Kapitan Kua GMA Cavite',
            ],
            [
                'username'   => 'responder_dizon',
                'email'      => 'dizon.patricia@local',
                'full_name'  => 'Patricia Dizon',
                'first_name' => 'Patricia',
                'last_name'  => 'Dizon',
                'mobile_no'  => '09171000016',
                'birth_date' => '1998-08-02',
                'address'    => '#2 Brgy Benjamin Tirona GMA Cavite',
            ],
            [
                'username'   => 'responder_espiritu',
                'email'      => 'espiritu.jerome@local',
                'full_name'  => 'Jerome Espiritu',
                'first_name' => 'Jerome',
                'last_name'  => 'Espiritu',
                'mobile_no'  => '09171000017',
                'birth_date' => '1985-01-19',
                'address'    => '#20 Brgy Bernardo Pulido GMA Cavite',
            ],
            [
                'username'   => 'responder_fernandez',
                'email'      => 'fernandez.claire@local',
                'full_name'  => 'Claire Fernandez',
                'first_name' => 'Claire',
                'last_name'  => 'Fernandez',
                'mobile_no'  => '09171000018',
                'birth_date' => '1992-04-11',
                'address'    => '#15 Brgy Francisco Reyes GMA Cavite',
            ],
            [
                'username'   => 'responder_gonzales',
                'email'      => 'gonzales.arvin@local',
                'full_name'  => 'Arvin Gonzales',
                'first_name' => 'Arvin',
                'last_name'  => 'Gonzales',
                'mobile_no'  => '09171000019',
                'birth_date' => '1994-06-28',
                'address'    => '#16 Brgy Francisco De Castro GMA Cavite',
            ],
            [
                'username'   => 'responder_hernandez',
                'email'      => 'hernandez.nina@local',
                'full_name'  => 'Nina Hernandez',
                'first_name' => 'Nina',
                'last_name'  => 'Hernandez',
                'mobile_no'  => '09171000020',
                'birth_date' => '1996-09-04',
                'address'    => '#1 Brgy Aldiano Olaes GMA Cavite',
            ],
        ];

        $responderPermissionId = Permission::where('slug', 'responder_access')->pluck('id')->first();

        foreach ($responders as $responderData) {
            $user = User::firstOrCreate(
                ['email' => $responderData['email']],
                [
                    'username'           => $responderData['username'],
                    'full_name'          => $responderData['full_name'],
                    'first_name'         => $responderData['first_name'],
                    'last_name'          => $responderData['last_name'],
                    'role'               => 'responder',
                    'mobile_no'          => $responderData['mobile_no'],
                    'birth_date'         => $responderData['birth_date'],
                    'barangay_id'        => null,
                    'address'            => $responderData['address'],
                    'password'           => Hash::make('password'),
                    'email_verified_at'  => $now,
                    'middle_name'       => null,
                    'site_location_id'  => null,
                    'valid_id'          => null,
                    'admin_verified'    => 'verified',
                ]
            );

            if ($responderPermissionId) {
                $user->permissions()->sync([$responderPermissionId]);
            }

            Responder::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'is_active'  => 1,
                    'created_by' => $user->id,
                ]
            );
        }
    }
}

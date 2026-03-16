<?php

namespace Database\Seeders;

use App\Models\SiteLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function Symfony\Component\String\s;

class SiteLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'site_name' => 'CarSiGMA District Hospital',
                'site_type' => 'Hospital',
                'site_category' => 'Health Facility',
                'coordinates' => '14.298667400356235, 121.00623458384493',
            ],
            [
                'site_name' => 'GMA Municipal Health Office',
                'site_type' => 'Health Office',
                'site_category' => 'Health Facility',
                'coordinates' => '14.310028746377482, 121.01523965136086',
            ],
            [
                'site_name' => 'Prime Dialysis Center',
                'site_type' => 'Dialysis Center',
                'site_category' => 'Health Facility',
                'coordinates' => '14.305010284737175, 121.00493960129428',
            ],
            [
                'site_name' => 'Reiwa Dialysis Center Inc.',
                'site_type' => 'Dialysis Center',
                'site_category' => 'Health Facility',
                'coordinates' => '14.291869130407868, 121.0025363421075',
            ],
            [
                'site_name' => 'GMA Municipal Police Station',
                'site_type' => 'Police Station',
                'site_category' => 'Emergency Response Facility',
                'coordinates' => '14.296095481688345, 121.00711872137883',
            ],
            [
                'site_name' => 'Cavite Provincial Police Office',
                'site_type' => 'Police Station',
                'site_category' => 'Emergency Response Facility',
                'coordinates' => '14.296417775787152, 121.00714017905013',
            ],
            [
                'site_name' => 'Gen. Mariano Alvarez Fire Station',
                'site_type' => 'Fire Station',
                'site_category' => 'Emergency Response Facility',
                'coordinates' => '14.298017902122808, 121.00761191501579',
            ],
            [
                'site_name' => 'Agape Lying-in Clinic',
                'site_type' => 'Lying-in Clinic',
                'site_category' => 'Health Facility',
                'coordinates' => '14.2938582184542, 120.99669976356205',
            ],
            [
                'site_name' => 'Elisa C. Conception Lying - In Clinic',
                'site_type' => 'Lying-in Clinic',
                'site_category' => 'Health Facility',
                'coordinates' => '14.315109237041142, 121.02572012227753',
            ],
            [
                'site_name' => 'N B SEGODINE LYING IN',
                'site_type' => 'Lying-in Clinic',
                'site_category' => 'Health Facility',
                'coordinates' => '14.33307230769884, 121.03395986806078',
            ],
            [
                'site_name' => 'Estella Lying-in Clinic',
                'site_type' => 'Lying-in Clinic',
                'site_category' => 'Health Facility',
                'coordinates' => '14.33606601296519, 121.04597616399471',
            ],
            [
                'site_name' => 'Tuatis Medical Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.289815325582769, 121.00811096934956',
            ],
            [
                'site_name' => 'Policare Medical Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.293803055858174, 121.0184286166396',
            ],
            [
                'site_name' => 'EMG Multi-Specialty Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.295977937117241, 121.01543612061658',
            ],
            [
                'site_name' => 'Bethany Medical Health Services GMA Branch',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.32026268145565, 121.02927641472313',
            ],
            [
                'site_name' => 'Bethany Medical Health Services GMA Branch',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.32026268145565, 121.02927641472313',
            ],
            [
                'site_name' => 'Mauricio Medical Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.292996031152379, 121.01923054247835',
            ],
            [
                'site_name' => 'Health Craft MEDICAL CLINIC',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.305011641129335, 121.02171049780266',
            ],
            [
                'site_name' => 'Mary Angels Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.298927074570402, 121.01377342871375',
            ],
            [
                'site_name' => 'Fatore Medical Diagnostic Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.313582167062455, 121.0295148489394',
            ],
            [
                'site_name' => 'MAXIMD Medical and Diagnostic Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.296649238027745, 121.0104074845611',
            ],
            [
                'site_name' => 'Dra Vida G. Rubio Memorial Medical Clinic',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.291726143216211, 121.00423680538505',
            ],
            [
                'site_name' => 'On Top Medical Services',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.283320971841116, 121.00023018898105',
            ],
            [
                'site_name' => 'Starcare Medical and Laboratory Services',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.327551321896355, 121.03535205880519',
            ],
            [
                'site_name' => 'INSORIO MULTISPECIALTY CLINIC & LABORATORY GMA CAVITE BRANCH',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.285539917453423, 121.00100781689399',
            ],
            [
                'site_name' => 'South Place',
                'site_type' => 'Private Clinic',
                'site_category' => 'Private Health Facility',
                'coordinates' => '14.282817060960644, 121.00124121233935',
            ],


        ];

        foreach ($locations as $location) {
            SiteLocation::firstOrCreate(
                ['site_name' => $location['site_name']],
                $location
            );
        }
    }
}

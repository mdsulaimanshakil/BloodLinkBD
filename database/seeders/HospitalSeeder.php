<?php

namespace Database\Seeders;

use App\Models\Hospital;
use Illuminate\Database\Seeder;

class HospitalSeeder extends Seeder
{
    /**
     * Prompt 15: Seed sample hospitals and blood banks in Khulna, Dhaka, and Chittagong.
     * Coordinates sourced from OpenStreetMap / Google Maps (publicly available).
     */
    public function run(): void
    {
        $hospitals = [
            // ─── DHAKA ────────────────────────────────────────────────────────────
            [
                'name'      => 'Dhaka Medical College Hospital',
                'district'  => 'Dhaka',
                'address'   => 'Bakshibazar, Dhaka 1000',
                'contact'   => '02-55165001',
                'type'      => 'hospital',
                'latitude'  => 23.7216,
                'longitude' => 90.3962,
            ],
            [
                'name'      => 'Bangabandhu Sheikh Mujib Medical University',
                'district'  => 'Dhaka',
                'address'   => 'Shahbag, Dhaka 1000',
                'contact'   => '02-9661067',
                'type'      => 'hospital',
                'latitude'  => 23.7347,
                'longitude' => 90.3964,
            ],
            [
                'name'      => 'National Institute of Cardiovascular Diseases',
                'district'  => 'Dhaka',
                'address'   => 'Sher-E-Bangla Nagar, Dhaka 1207',
                'contact'   => '02-9126401',
                'type'      => 'hospital',
                'latitude'  => 23.7736,
                'longitude' => 90.3626,
            ],
            [
                'name'      => 'Dhaka Shishu Hospital',
                'district'  => 'Dhaka',
                'address'   => 'Sher-E-Bangla Nagar, Dhaka 1207',
                'contact'   => '02-9101427',
                'type'      => 'hospital',
                'latitude'  => 23.7717,
                'longitude' => 90.3636,
            ],
            [
                'name'      => 'Bangladesh Red Crescent Society Blood Bank',
                'district'  => 'Dhaka',
                'address'   => '1 Outer Circular Road, Moghbazar, Dhaka 1217',
                'contact'   => '02-9330188',
                'type'      => 'blood_bank',
                'latitude'  => 23.7460,
                'longitude' => 90.4070,
            ],
            [
                'name'      => 'Quantum Blood Bank',
                'district'  => 'Dhaka',
                'address'   => '23 Kazi Nazrul Islam Ave, Dhaka 1000',
                'contact'   => '01713-092620',
                'type'      => 'blood_bank',
                'latitude'  => 23.7389,
                'longitude' => 90.3916,
            ],
            [
                'name'      => 'Sandhani National Eye Hospital',
                'district'  => 'Dhaka',
                'address'   => 'Bakshibazar, Dhaka 1000',
                'contact'   => '02-7317474',
                'type'      => 'hospital',
                'latitude'  => 23.7212,
                'longitude' => 90.3949,
            ],

            // ─── CHITTAGONG ────────────────────────────────────────────────────────
            [
                'name'      => 'Chittagong Medical College Hospital',
                'district'  => 'Chittagong',
                'address'   => 'K. B. Fazlul Kader Road, Chittagong 4203',
                'contact'   => '031-618850',
                'type'      => 'hospital',
                'latitude'  => 22.3609,
                'longitude' => 91.8219,
            ],
            [
                'name'      => 'Chittagong General Hospital',
                'district'  => 'Chittagong',
                'address'   => 'Station Road, Chittagong 4000',
                'contact'   => '031-617991',
                'type'      => 'hospital',
                'latitude'  => 22.3375,
                'longitude' => 91.8318,
            ],
            [
                'name'      => 'CMCH Blood Bank',
                'district'  => 'Chittagong',
                'address'   => 'K. B. Fazlul Kader Road, Chittagong 4203',
                'contact'   => '031-619440',
                'type'      => 'blood_bank',
                'latitude'  => 22.3611,
                'longitude' => 91.8221,
            ],
            [
                'name'      => 'Pahartali General Hospital',
                'district'  => 'Chittagong',
                'address'   => 'Pahartali, Chittagong 4202',
                'contact'   => '031-750921',
                'type'      => 'hospital',
                'latitude'  => 22.3948,
                'longitude' => 91.7742,
            ],
            [
                'name'      => 'Quantum Blood Bank Chittagong',
                'district'  => 'Chittagong',
                'address'   => 'GEC Circle, Chittagong 4000',
                'contact'   => '01713-092622',
                'type'      => 'blood_bank',
                'latitude'  => 22.3569,
                'longitude' => 91.8175,
            ],

            // ─── KHULNA ────────────────────────────────────────────────────────────
            [
                'name'      => 'Khulna Medical College Hospital',
                'district'  => 'Khulna',
                'address'   => 'KDA Ave, Khulna 9000',
                'contact'   => '041-731026',
                'type'      => 'hospital',
                'latitude'  => 22.8156,
                'longitude' => 89.5465,
            ],
            [
                'name'      => 'Khulna General Hospital',
                'district'  => 'Khulna',
                'address'   => 'Hospital Road, Khulna 9100',
                'contact'   => '041-725266',
                'type'      => 'hospital',
                'latitude'  => 22.8100,
                'longitude' => 89.5521,
            ],
            [
                'name'      => 'KMCH Blood Bank',
                'district'  => 'Khulna',
                'address'   => 'KDA Ave, Khulna 9000',
                'contact'   => '041-731027',
                'type'      => 'blood_bank',
                'latitude'  => 22.8157,
                'longitude' => 89.5467,
            ],
            [
                'name'      => 'Khulna Shishu Hospital',
                'district'  => 'Khulna',
                'address'   => 'Hospital Road, Khulna 9100',
                'contact'   => '041-725300',
                'type'      => 'hospital',
                'latitude'  => 22.8090,
                'longitude' => 89.5514,
            ],
            [
                'name'      => 'Red Crescent Blood Bank Khulna',
                'district'  => 'Khulna',
                'address'   => 'Shibbari, Khulna 9100',
                'contact'   => '041-726101',
                'type'      => 'blood_bank',
                'latitude'  => 22.8135,
                'longitude' => 89.5498,
            ],
        ];

        foreach ($hospitals as $data) {
            Hospital::firstOrCreate(
                ['name' => $data['name'], 'district' => $data['district']],
                $data
            );
        }

        $this->command->info('✅ ' . count($hospitals) . ' hospitals/blood banks seeded for Dhaka, Chittagong & Khulna.');
    }
}

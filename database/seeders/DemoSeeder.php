<?php

namespace Database\Seeders;

use App\Models\BloodRequest;
use App\Models\DonationHistory;
use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Prompt 22: Demo data seeder — realistic Bangladeshi data.
 *
 * Seeds:
 * - 1 admin user
 * - 50 donor users with profiles across 8 districts
 * - 20 sample blood requests at varying urgency/status (including expired)
 * - Donation history for some donors (for trust_score / badge demo)
 */
class DemoSeeder extends Seeder
{
    /** Blood groups (weighted towards O+ and B+ — most common in BD) */
    private array $bloodGroups = [
        'A+', 'A+', 'A-',
        'B+', 'B+', 'B+', 'B-',
        'AB+', 'AB-',
        'O+', 'O+', 'O+', 'O-',
    ];

    /** 8 districts with hospitals for demo */
    private array $districts = [
        'Dhaka', 'Chittagong', 'Khulna', 'Rajshahi',
        'Sylhet', 'Barishal', 'Comilla', 'Mymensingh',
    ];

    private array $hospitals = [
        'Dhaka'      => ['Dhaka Medical College Hospital', 'BSMMU Hospital', 'National Heart Foundation'],
        'Chittagong' => ['Chittagong Medical College Hospital', 'Chittagong General Hospital'],
        'Khulna'     => ['Khulna Medical College Hospital', 'Khulna General Hospital'],
        'Rajshahi'   => ['Rajshahi Medical College Hospital', 'Rajshahi General Hospital'],
        'Sylhet'     => ['Sylhet MAG Osmani Medical College Hospital', 'North East Medical College Hospital'],
        'Barishal'   => ['Sher-E-Bangla Medical College Hospital', 'Barishal General Hospital'],
        'Comilla'    => ['Comilla Medical College Hospital', 'Comilla General Hospital'],
        'Mymensingh' => ['Mymensingh Medical College Hospital', 'Community Medical College Hospital'],
    ];

    /** Realistic Bangladeshi first names */
    private array $firstNames = [
        'Md.', 'Mohammad', 'Rahim', 'Karim', 'Hassan', 'Hossain', 'Ali', 'Islam',
        'Fatima', 'Ayesha', 'Nusrat', 'Sadia', 'Tanvir', 'Sohel', 'Arif', 'Rony',
        'Sabbir', 'Shakib', 'Mushfiq', 'Rubel', 'Liton', 'Tamim', 'Mehedi', 'Nahid',
        'Rasel', 'Mamun', 'Shafiqul', 'Mizanur', 'Hafizur', 'Jahangir',
    ];

    private array $lastNames = [
        'Rahman', 'Islam', 'Hossain', 'Ahmed', 'Khan', 'Begum', 'Akter', 'Khatun',
        'Sarker', 'Biswas', 'Das', 'Roy', 'Mia', 'Mondal', 'Sheikh', 'Molla',
        'Uddin', 'Chowdhury', 'Talukder', 'Bhuiyan', 'Sikder', 'Siddique',
    ];

    public function run(): void
    {
        // ── 1. Admin user ────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@bloodlinkbd.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
            ]
        );
        $this->command->info('✅ Admin user: admin@bloodlinkbd.com / Admin@1234');

        // ── 2. 50 Donors across 8 districts ─────────────────────────────
        $donors = [];
        $phoneBase = 1712340000;

        for ($i = 1; $i <= 50; $i++) {
            $firstName   = $this->firstNames[array_rand($this->firstNames)];
            $lastName    = $this->lastNames[array_rand($this->lastNames)];
            $name        = $firstName . ' ' . $lastName;
            $district    = $this->districts[($i - 1) % 8]; // round-robin across 8 districts
            $bloodGroup  = $this->bloodGroups[array_rand($this->bloodGroups)];
            $phone       = '0' . ($phoneBase + $i);
            $email       = 'donor' . $i . '@demo.bloodlinkbd.com';

            // Vary donation history: some have 0, some have 1-2, a few have 3+
            $donationCount   = match (true) {
                $i <= 10  => 0,
                $i <= 25  => random_int(1, 2),
                $i <= 40  => random_int(2, 4),
                default   => random_int(3, 8),
            };

            $lastDonationDate = $donationCount > 0
                ? Carbon::now()->subDays(random_int(91, 365))->toDateString()
                : null;

            $trustScore = $donationCount > 0
                ? round(random_int(30, 50) / 10, 2)  // 3.0 – 5.0
                : 0.00;

            // Some donors are unavailable (on cooldown demo)
            $isAvailable = $donationCount === 0 || $lastDonationDate === null
                || Carbon::parse($lastDonationDate)->lt(Carbon::now()->subDays(90));

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $name,
                    'password' => Hash::make('password'),
                    'role'     => 'donor',
                ]
            );

            $profile = DonorProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'blood_group'        => $bloodGroup,
                    'district'           => $district,
                    'phone'              => $phone,
                    'last_donation_date' => $lastDonationDate,
                    'is_available'       => $isAvailable,
                    'is_verified'        => $i <= 45, // 45 verified, 5 pending
                    'donation_count'     => $donationCount,
                    'trust_score'        => $trustScore,
                ]
            );

            $donors[] = ['user' => $user, 'profile' => $profile];
        }

        $this->command->info('✅ 50 donor profiles created (45 verified, 5 pending).');

        // ── 3. 20 Sample Blood Requests (varied urgency + status) ────────
        $requestData = [
            // Active — Critical (5)
            ['urgency' => 'critical', 'status' => 'active',    'blood_group' => 'O-', 'district' => 'Dhaka',      'days_ago' => 0],
            ['urgency' => 'critical', 'status' => 'active',    'blood_group' => 'AB+','district' => 'Chittagong',  'days_ago' => 0],
            ['urgency' => 'critical', 'status' => 'active',    'blood_group' => 'B-', 'district' => 'Khulna',     'days_ago' => 1],
            ['urgency' => 'critical', 'status' => 'active',    'blood_group' => 'O+', 'district' => 'Rajshahi',   'days_ago' => 1],
            ['urgency' => 'critical', 'status' => 'active',    'blood_group' => 'A-', 'district' => 'Sylhet',     'days_ago' => 2],

            // Active — Urgent (5)
            ['urgency' => 'urgent',   'status' => 'active',    'blood_group' => 'B+', 'district' => 'Dhaka',      'days_ago' => 1],
            ['urgency' => 'urgent',   'status' => 'active',    'blood_group' => 'A+', 'district' => 'Barishal',   'days_ago' => 2],
            ['urgency' => 'urgent',   'status' => 'active',    'blood_group' => 'O+', 'district' => 'Comilla',    'days_ago' => 3],
            ['urgency' => 'urgent',   'status' => 'active',    'blood_group' => 'AB-','district' => 'Mymensingh', 'days_ago' => 2],
            ['urgency' => 'urgent',   'status' => 'active',    'blood_group' => 'O-', 'district' => 'Chittagong', 'days_ago' => 1],

            // Active — Normal (3)
            ['urgency' => 'normal',   'status' => 'active',    'blood_group' => 'A+', 'district' => 'Dhaka',      'days_ago' => 4],
            ['urgency' => 'normal',   'status' => 'active',    'blood_group' => 'B+', 'district' => 'Khulna',     'days_ago' => 5],
            ['urgency' => 'normal',   'status' => 'active',    'blood_group' => 'O+', 'district' => 'Rajshahi',   'days_ago' => 6],

            // Fulfilled (4)
            ['urgency' => 'urgent',   'status' => 'fulfilled', 'blood_group' => 'A+', 'district' => 'Dhaka',      'days_ago' => 10],
            ['urgency' => 'critical', 'status' => 'fulfilled', 'blood_group' => 'O-', 'district' => 'Chittagong', 'days_ago' => 15],
            ['urgency' => 'normal',   'status' => 'fulfilled', 'blood_group' => 'B+', 'district' => 'Sylhet',     'days_ago' => 20],
            ['urgency' => 'urgent',   'status' => 'fulfilled', 'blood_group' => 'O+', 'district' => 'Barishal',   'days_ago' => 25],

            // Expired (2)
            ['urgency' => 'normal',   'status' => 'expired',   'blood_group' => 'A-', 'district' => 'Comilla',    'days_ago' => 8],
            ['urgency' => 'urgent',   'status' => 'expired',   'blood_group' => 'B-', 'district' => 'Mymensingh', 'days_ago' => 12],

            // Removed (1 — admin removed as fake)
            ['urgency' => 'critical', 'status' => 'removed',   'blood_group' => 'AB+','district' => 'Dhaka',      'days_ago' => 5],
        ];

        $patientNames = [
            'Rahim Uddin', 'Fatima Begum', 'Kamal Hossain', 'Nasrin Akter', 'Jamal Khan',
            'Sumaiya Islam', 'Rafiqul Haque', 'Kohinoor Begum', 'Tariq Ahmed', 'Rima Das',
            'Anwar Hossain', 'Shamima Khatun', 'Belal Mia', 'Roksana Parvin', 'Shahidul Islam',
            'Moriam Begum', 'Abul Kalam', 'Tahmina Akter', 'Monirul Islam', 'Hasina Begum',
        ];

        $phoneIdx = 0;
        foreach ($requestData as $idx => $data) {
            $hospital = $this->hospitals[$data['district']][0];
            $createdAt = Carbon::now()->subDays($data['days_ago'])->subHours(random_int(0, 12));

            // Set expires_at based on urgency
            $hours     = ['critical' => 48, 'urgent' => 96, 'normal' => 168][$data['urgency']];
            $expiresAt = $createdAt->copy()->addHours($hours);

            BloodRequest::create([
                'patient_name'    => $patientNames[$idx],
                'blood_group'     => $data['blood_group'],
                'district'        => $data['district'],
                'hospital'        => $hospital,
                'urgency'         => $data['urgency'],
                'status'          => $data['status'],
                'requester_phone' => '017' . str_pad(random_int(10000000, 99999999), 8, '0'),
                'additional_notes'=> $idx % 3 === 0 ? 'Urgently needed for surgery. Please contact ASAP.' : null,
                'expires_at'      => $expiresAt,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);

            $phoneIdx++;
        }

        $this->command->info('✅ 20 sample blood requests seeded (13 active, 4 fulfilled, 2 expired, 1 removed).');

        // ── 4. Donation History for demo badge / trust_score ─────────────
        $fulfilledRequests = BloodRequest::where('status', 'fulfilled')->get();
        $verifiedDonors    = collect($donors)
            ->filter(fn($d) => $d['profile']->is_verified && $d['profile']->donation_count > 0)
            ->values();

        foreach ($verifiedDonors->take(15) as $idx => $donorData) {
            $profile = $donorData['profile'];
            $request = $fulfilledRequests->count() > 0
                ? $fulfilledRequests[$idx % $fulfilledRequests->count()]
                : null;

            for ($j = 0; $j < min($profile->donation_count, 3); $j++) {
                DonationHistory::firstOrCreate(
                    [
                        'donor_id'        => $profile->user_id,
                        'donated_at'      => Carbon::now()->subDays(($j + 1) * random_int(90, 180))->toDateString(),
                    ],
                    [
                        'blood_request_id'=> $request?->id,
                        'hospital'        => $this->hospitals[$profile->district][0] ?? 'General Hospital',
                        'district'        => $profile->district,
                        'rating'          => random_int(3, 5),
                        'feedback_notes'  => $j === 0 ? 'Very cooperative and punctual. Great experience!' : null,
                    ]
                );
            }
        }

        $this->command->info('✅ Donation history records created for demo donors.');
        $this->command->newLine();
        $this->command->info('🩸 Demo seeding complete! Login credentials:');
        $this->command->line('   Admin:   admin@bloodlinkbd.com / Admin@1234');
        $this->command->line('   Donor 1: donor1@demo.bloodlinkbd.com / password');
    }
}

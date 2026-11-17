<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Inquiry;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);

        $admin = User::create(['name' => 'Gym Admin', 'email' => 'admin@gym.test', 'phone' => '+92 300 0000001', 'role' => 'admin', 'password' => 'password']);
        User::create(['name' => 'Front Desk', 'email' => 'staff@gym.test', 'phone' => '+92 300 0000002', 'role' => 'staff', 'password' => 'password']);

        $plans = collect([
            ['Monthly Basic', 30, 3000, "Gym floor access\nLocker room\nFree fitness assessment"],
            ['Quarterly Pro', 90, 8000, "Everything in Basic\nGroup classes\n1 personal training session\nDiet consultation"],
            ['Half-Yearly Elite', 180, 15000, "Everything in Pro\n4 personal training sessions\nSteam & sauna\nGuest pass x2"],
            ['Annual Champion', 365, 27000, "Everything in Elite\nDedicated trainer\nMonthly body composition\nFree gym kit"],
        ])->map(fn ($p) => MembershipPlan::create([
            'name' => $p[0], 'duration_days' => $p[1], 'price' => $p[2], 'features' => $p[3],
            'description' => $p[0] . ' membership', 'is_active' => true,
        ]));

        $trainers = collect([
            ['Ali Raza', 'male', 'Strength & Conditioning', 8, 60000],
            ['Sara Khan', 'female', 'Yoga & Flexibility', 6, 55000],
            ['Usman Tariq', 'male', 'Bodybuilding', 10, 70000],
            ['Ayesha Malik', 'female', 'Weight Loss & HIIT', 5, 50000],
            ['Bilal Ahmed', 'male', 'CrossFit', 4, 45000],
        ])->map(fn ($t, $i) => Trainer::create([
            'name' => $t[0], 'email' => strtolower(str_replace(' ', '.', $t[0])) . '@gym.test', 'phone' => '+92 301 55500' . $i,
            'gender' => $t[1], 'specialization' => $t[2], 'experience_years' => $t[3], 'salary' => $t[4],
            'joined_at' => now()->subYears(2)->subMonths($i), 'bio' => "Certified {$t[2]} coach.", 'status' => 'active',
        ]));

        $first = ['Ahmed', 'Hamza', 'Fatima', 'Zainab', 'Hassan', 'Ayesha', 'Omar', 'Hira', 'Saad', 'Maryam', 'Danish', 'Iqra', 'Faisal', 'Noor', 'Imran', 'Sana', 'Kashif', 'Amna', 'Talha', 'Rabia', 'Waqas', 'Mehwish', 'Junaid', 'Laiba', 'Shahzaib', 'Areeba', 'Fahad', 'Komal', 'Asad', 'Hafsa'];
        $last = ['Khan', 'Ali', 'Sheikh', 'Butt', 'Qureshi', 'Malik', 'Chaudhry', 'Hussain', 'Siddiqui', 'Javed'];
        $femaleNames = ['Fatima', 'Zainab', 'Ayesha', 'Hira', 'Maryam', 'Iqra', 'Noor', 'Sana', 'Amna', 'Rabia', 'Mehwish', 'Laiba', 'Areeba', 'Komal', 'Hafsa'];

        foreach ($first as $i => $fn) {
            $name = $fn . ' ' . $last[$i % count($last)];
            $joined = now()->subDays(mt_rand(5, 330));
            $member = Member::create([
                'name' => $name, 'email' => strtolower($fn) . $i . '@mail.test', 'phone' => '+92 3' . mt_rand(10, 49) . ' ' . mt_rand(1000000, 9999999),
                'gender' => in_array($fn, $femaleNames) ? 'female' : 'male', 'dob' => now()->subYears(mt_rand(18, 45))->subDays(mt_rand(0, 360)),
                'address' => 'House ' . mt_rand(1, 300) . ', Block ' . chr(65 + $i % 8) . ', Lahore', 'emergency_contact' => '+92 333 ' . mt_rand(1000000, 9999999),
                'height_cm' => mt_rand(155, 190), 'weight_kg' => mt_rand(52, 98), 'trainer_id' => $i % 3 === 0 ? null : $trainers[$i % 5]->id,
                'joined_at' => $joined, 'status' => $i % 13 === 12 ? 'inactive' : 'active',
            ]);

            // subscription history
            $start = $joined->copy();
            while ($start->lt(now())) {
                $plan = $plans[mt_rand(0, 3) === 0 ? mt_rand(1, 3) : 0];
                $end = $start->copy()->addDays($plan->duration_days);
                $discount = mt_rand(0, 4) === 0 ? 500 : 0;
                $sub = Subscription::create([
                    'member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => $start, 'end_date' => $end,
                    'amount' => $plan->price, 'discount' => $discount, 'status' => $end->lt(now()) ? 'expired' : 'active',
                ]);
                $net = $plan->price - $discount;
                $paid = ($end->gt(now()) && mt_rand(0, 3) === 0) ? round($net / 2) : $net; // a few partial payments
                Payment::create([
                    'member_id' => $member->id, 'subscription_id' => $sub->id, 'amount' => $paid,
                    'method' => ['cash', 'cash', 'card', 'bank_transfer', 'online'][mt_rand(0, 4)],
                    'paid_at' => $start->copy()->min(now()), 'received_by' => $admin->id,
                ]);
                if ($i % 7 === 6 && $end->gt(now()->subDays(40))) break; // some members lapse
                $start = $end->copy()->addDay();
            }

            // attendance for last 30 days
            if ($member->status === 'active') {
                for ($d = 30; $d >= 0; $d--) {
                    if (mt_rand(0, 100) < 45) {
                        $in = sprintf('%02d:%02d:00', mt_rand(6, 20), mt_rand(0, 59));
                        Attendance::create([
                            'member_id' => $member->id, 'date' => now()->subDays($d)->toDateString(), 'check_in' => $in,
                            'check_out' => $d === 0 && mt_rand(0, 1) ? null : date('H:i:s', strtotime($in) + mt_rand(45, 120) * 60),
                        ]);
                    }
                }
            }
        }

        // expenses for 12 months
        for ($m = 11; $m >= 0; $m--) {
            $date = now()->subMonths($m)->startOfMonth();
            Expense::create(['title' => 'Monthly rent', 'category' => 'rent', 'amount' => 18000, 'spent_on' => $date->copy()->addDays(1)]);
            Expense::create(['title' => 'Electricity & water', 'category' => 'utilities', 'amount' => mt_rand(4000, 8000), 'spent_on' => $date->copy()->addDays(9)]);
            Expense::create(['title' => 'Trainer salaries', 'category' => 'salary', 'amount' => 15000, 'spent_on' => $m === 0 ? now()->startOfMonth() : $date->copy()->addDays(27)]);
            if ($m % 3 === 0) Expense::create(['title' => 'Equipment servicing', 'category' => 'maintenance', 'amount' => mt_rand(2000, 5000), 'spent_on' => $date->copy()->addDays(14)]);
            if ($m % 4 === 1) Expense::create(['title' => 'Social media ads', 'category' => 'marketing', 'amount' => mt_rand(3000, 6000), 'spent_on' => $date->copy()->addDays(5)]);
        }
        Expense::create(['title' => 'New dumbbell set', 'category' => 'equipment', 'amount' => 25000, 'spent_on' => now()->subMonths(2)->startOfMonth()->addDays(12)]);

        // inquiries
        foreach ([['Zeeshan Haider', 'new'], ['Mahnoor Aslam', 'new'], ['Rehan Ali', 'contacted'], ['Kiran Shah', 'converted'], ['Adeel Nawaz', 'closed']] as $k => [$n, $s]) {
            Inquiry::create([
                'name' => $n, 'email' => strtolower(explode(' ', $n)[0]) . '@mail.test', 'phone' => '+92 345 ' . mt_rand(1000000, 9999999),
                'membership_plan_id' => $plans[$k % 4]->id, 'message' => 'I would like to visit the gym and know about the timings and trainer availability.', 'status' => $s,
            ]);
        }
    }
}

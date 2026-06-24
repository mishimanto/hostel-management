<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\HostelNotification;
use App\Models\Payment;
use App\Models\ResidentProfile;
use App\Models\Room;
use App\Models\Seat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $mainBranch = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Branch',
                'phone' => '+8801711000000',
                'address' => 'House 12, Road 4, Dhaka',
                'rent_due_day' => 5,
            ]
        );

        $northBranch = Branch::firstOrCreate(
            ['code' => 'NORTH'],
            [
                'name' => 'North Branch',
                'phone' => '+8801722000000',
                'address' => 'Block B, Uttara, Dhaka',
                'rent_due_day' => 7,
            ]
        );

        $room201 = Room::firstOrCreate(['branch_id' => $mainBranch->id, 'room_no' => '201'], ['capacity' => 2, 'floor' => '2nd']);
        $room301 = Room::firstOrCreate(['branch_id' => $mainBranch->id, 'room_no' => '301'], ['capacity' => 3, 'floor' => '3rd']);
        $room101 = Room::firstOrCreate(['branch_id' => $northBranch->id, 'room_no' => '101'], ['capacity' => 1, 'floor' => '1st']);

        $seatA = Seat::firstOrCreate(['room_id' => $room201->id, 'label' => 'A'], ['monthly_rent' => 8500, 'security_deposit' => 10000, 'is_available' => false]);
        Seat::firstOrCreate(['room_id' => $room201->id, 'label' => 'B'], ['monthly_rent' => 8500, 'security_deposit' => 10000]);
        Seat::firstOrCreate(['room_id' => $room301->id, 'label' => 'A'], ['monthly_rent' => 6500, 'security_deposit' => 8000]);
        Seat::firstOrCreate(['room_id' => $room301->id, 'label' => 'B'], ['monthly_rent' => 6500, 'security_deposit' => 8000]);
        Seat::firstOrCreate(['room_id' => $room101->id, 'label' => 'A'], ['monthly_rent' => 12000, 'security_deposit' => 15000]);

        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'phone' => '+8801700000000',
            'nid_number' => 'NID-1234567890',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Hostel Admin',
            'phone' => '+8801900000000',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        ResidentProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'branch_id' => $mainBranch->id,
                'room_id' => $room201->id,
                'seat_id' => $seatA->id,
                'joined_at' => now()->subMonths(6)->toDateString(),
                'balance' => 1500,
                'deposit_paid' => 10000,
                'guardian_name' => 'Guardian User',
                'guardian_phone' => '+8801800000000',
            ]
        );

        Payment::firstOrCreate([
            'invoice_no' => 'INV-'.now()->format('Ym').'-001',
        ], [
            'user_id' => $user->id,
            'seat_id' => $seatA->id,
            'billing_month' => now()->startOfMonth()->toDateString(),
            'due_date' => Carbon::create(now()->year, now()->month, $mainBranch->rent_due_day)->toDateString(),
            'amount_due' => 8500,
            'amount_paid' => 7000,
            'adjustment_amount' => 1500,
            'transaction_id' => 'BKASH-DEMO-001',
        ]);

        Payment::firstOrCreate([
            'invoice_no' => 'INV-'.now()->subMonth()->format('Ym').'-001',
        ], [
            'user_id' => $user->id,
            'seat_id' => $seatA->id,
            'billing_month' => now()->subMonth()->startOfMonth()->toDateString(),
            'due_date' => now()->subMonth()->setDay($mainBranch->rent_due_day)->toDateString(),
            'amount_due' => 8500,
            'amount_paid' => 8500,
            'transaction_id' => 'BKASH-DEMO-000',
        ]);

        HostelNotification::firstOrCreate([
            'user_id' => $user->id,
            'title' => 'Rent reminder',
        ], [
            'body' => 'Your monthly rent is due before the branch due date.',
            'type' => 'rent',
        ]);

        HostelNotification::firstOrCreate([
            'user_id' => $user->id,
            'title' => 'Welcome to the hostel portal',
        ], [
            'body' => 'You can request seat changes, leaves, exits, and track payments here.',
            'type' => 'announcement',
        ]);
    }
}

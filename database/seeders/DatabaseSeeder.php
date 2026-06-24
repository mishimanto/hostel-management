<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $mainBranch = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Branch', 'phone' => '+8801711000000', 'address' => 'House 12, Road 4, Dhaka', 'rent_due_day' => 5]
        );

        $northBranch = Branch::firstOrCreate(
            ['code' => 'NORTH'],
            ['name' => 'North Branch', 'phone' => '+8801722000000', 'address' => 'Block B, Uttara, Dhaka', 'rent_due_day' => 7]
        );

        $room201 = Room::firstOrCreate(
            ['branch_id' => $mainBranch->id, 'room_number' => '201'],
            ['monthly_rent' => 8500, 'description' => 'Bright room with attached balcony.', 'status' => 'booked']
        );

        Room::firstOrCreate(
            ['branch_id' => $mainBranch->id, 'room_number' => '301'],
            ['monthly_rent' => 6500, 'description' => 'Budget room near study area.', 'status' => 'available']
        );

        Room::firstOrCreate(
            ['branch_id' => $northBranch->id, 'room_number' => '101'],
            ['monthly_rent' => 12000, 'description' => 'Private premium room.', 'status' => 'available']
        );

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

        $booking = RoomBooking::firstOrCreate(
            ['user_id' => $user->id, 'room_id' => $room201->id, 'status' => 'approved'],
            [
                'branch_id' => $mainBranch->id,
                'monthly_rent' => $room201->monthly_rent,
                'requested_start_date' => now()->subMonths(2)->toDateString(),
                'requested_end_date' => now()->addDays(12)->toDateString(),
                'total_days' => now()->subMonths(2)->startOfDay()->diffInDays(now()->addDays(12)->startOfDay()) + 1,
                'payable_amount' => 8500,
                'payment_method' => 'bKash',
                'transaction_id' => 'BKASH-DEMO-001',
                'payment_details' => 'Demo approved payment',
                'started_at' => now()->subMonths(2)->toDateString(),
                'paid_until' => now()->addDays(12)->toDateString(),
                'reviewed_at' => now(),
            ]
        );

        Payment::firstOrCreate([
            'invoice_no' => 'INV-'.now()->format('Ym').'-001',
        ], [
            'user_id' => $user->id,
            'room_booking_id' => $booking->id,
            'room_id' => $room201->id,
            'billing_month' => now()->startOfMonth()->toDateString(),
            'due_date' => Carbon::create(now()->year, now()->month, $mainBranch->rent_due_day)->toDateString(),
            'amount_due' => 8500,
            'amount_paid' => 8500,
            'transaction_id' => 'BKASH-DEMO-001',
        ]);

        Notification::firstOrCreate([
            'user_id' => $user->id,
            'title' => 'Welcome to the hostel portal',
        ], [
            'body' => 'You can manage room booking, rent history, room change and leave requests here.',
            'type' => 'announcement',
        ]);
    }
}

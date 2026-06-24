<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\User;
use App\Services\RoomChangeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomChangeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cheaper_room_converts_unused_value_to_extra_days(): void
    {
        $branch = Branch::create(['name' => 'Main', 'code' => 'MAIN', 'address' => 'Dhaka']);
        $oldRoom = Room::create(['branch_id' => $branch->id, 'room_number' => '201', 'monthly_rent' => 9000, 'status' => 'booked']);
        $newRoom = Room::create(['branch_id' => $branch->id, 'room_number' => '301', 'monthly_rent' => 6000, 'status' => 'available']);
        $user = User::create(['name' => 'Resident', 'email' => 'resident@example.com', 'password' => 'password']);

        $booking = RoomBooking::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'room_id' => $oldRoom->id,
            'monthly_rent' => 9000,
            'requested_start_date' => '2026-06-01',
            'requested_end_date' => '2026-06-16',
            'total_days' => 16,
            'payable_amount' => 4800,
            'payment_method' => 'bKash',
            'started_at' => '2026-06-01',
            'paid_until' => '2026-06-16',
            'status' => 'approved',
        ]);

        $preview = app(RoomChangeService::class)->preview($booking, $newRoom, Carbon::parse('2026-06-01'));

        $this->assertSame($booking->id, $preview['room_booking_id']);
        $this->assertEquals(15, $preview['remaining_paid_days']);
        $this->assertEquals(7, $preview['extra_days']);
        $this->assertSame(0.0, $preview['additional_payable']);
        $this->assertSame('2026-06-23', $preview['new_paid_until']);
    }
}

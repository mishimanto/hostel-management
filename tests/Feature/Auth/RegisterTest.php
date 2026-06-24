<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_is_not_admin(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Resident',
            'email' => 'NEW@EXAMPLE.COM',
            'phone' => '+8801712345678',
            'nid_number' => 'NID-TEST-1',
            'address' => 'Dhaka',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $this->assertSame(route('customer.dashboard'), $response->headers->get('Location'));
        $this->assertAuthenticated();

        $user = User::where('email', 'new@example.com')->firstOrFail();

        $this->assertFalse($user->is_admin);
        $this->assertSame('+8801712345678', $user->phone);
        $this->assertSame('NID-TEST-1', $user->nid_number);
        $this->assertSame('Dhaka', $user->address);
    }
}

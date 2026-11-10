<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_buyer_and_returns_token(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Новый покупатель',
            'email' => 'new.buyer@example.com',
            'password' => 'TestPassword!2026',
            'password_confirmation' => 'TestPassword!2026',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'new.buyer@example.com')
            ->assertJsonPath('data.role', 'buyer')
            ->assertJsonStructure(['token', 'token_type']);

        $this->assertDatabaseHas('users', [
            'email' => 'new.buyer@example.com',
            'role' => 'buyer',
        ]);
    }

    public function test_login_returns_token_and_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'login.buyer@example.com',
            'password' => Hash::make('CorrectPassword!2026'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'login.buyer@example.com',
            'password' => 'CorrectPassword!2026',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type']);

        $this->postJson('/api/auth/login', [
            'email' => 'login.buyer@example.com',
            'password' => 'WrongPassword!2026',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_endpoint_returns_current_user(): void
    {
        $user = User::factory()->create([
            'email' => 'current.user@example.com',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }
}
<?php

namespace Tests\Feature\Auth;

use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_successfully(): void
    {
        $payload = $this->validRegisterPayload([
            'email' => 'ana@example.test',
        ]);

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'User created successfully')
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role', 'avatar_url'],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.test',
        ]);

        $response = $this->postJson('/api/v1/auth/register', $this->validRegisterPayload([
            'email' => 'duplicate@example.test',
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'ValidationError')
            ->assertJsonStructure([
                'error' => [
                    'details' => ['email'],
                ],
            ]);
    }

    public function test_login_works_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email', 'role', 'avatar_url'],
            ])
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'wrong-password@example.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_refresh_token_generates_new_access_token(): void
    {
        $user = User::factory()->create([
            'email' => 'refresh@example.test',
            'password' => 'password',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $refreshToken = $login->json('refresh_token');

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ]);

        $this->assertNotSame($refreshToken, $response->json('refresh_token'));
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'Unauthorized');
    }

    public function test_logout_authenticated_user_works(): void
    {
        $user = User::factory()->create([
            'email' => 'logout@example.test',
            'password' => 'password',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this
            ->withToken($login->json('access_token'))
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJsonStructure(['message']);
    }

    private function validRegisterPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Professional',
            'email' => 'ana.professional@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }
}

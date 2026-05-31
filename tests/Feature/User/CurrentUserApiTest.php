<?php

namespace Tests\Feature\User;

use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentUserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_me_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('error.type', 'Unauthorized');
    }

    public function test_get_me_with_token_returns_current_user(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/me');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'User retrieved successfully')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_put_me_updates_allowed_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.test',
        ]);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.test',
            'avatar_url' => 'https://example.test/avatar.png',
        ];

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/me', $payload);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'User updated successfully')
            ->assertJsonPath('user.name', $payload['name'])
            ->assertJsonPath('user.email', $payload['email'])
            ->assertJsonPath('user.avatar_url', $payload['avatar_url']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'avatar_url' => $payload['avatar_url'],
        ]);
    }

    public function test_put_me_validates_unique_email(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.test',
        ]);
        $otherUser = User::factory()->create([
            'email' => 'taken@example.test',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/me', [
                'email' => $otherUser->email,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error.type', 'ValidationError')
            ->assertJsonStructure([
                'error' => [
                    'details' => ['email'],
                ],
            ]);
    }

    public function test_put_me_updates_password_and_allows_login_with_new_password(): void
    {
        $user = User::factory()->create([
            'email' => 'password-change@example.test',
            'password' => 'old-password',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/me', [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertOk();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'new-password',
        ]);

        $login
            ->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token']);
    }

    private function authHeaders(User $user): array
    {
        $token = auth('user_jwt')->login($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }
}

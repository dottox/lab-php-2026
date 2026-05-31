<?php

namespace Tests\Feature;

use App\Models\Service\Service;
use App\Models\User\ProfessionalProfile;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_exception_returns_consistent_json(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'ValidationError')
            ->assertJsonStructure([
                'error' => [
                    'message',
                    'details' => ['name', 'email', 'password'],
                ],
            ]);
    }

    public function test_authentication_exception_returns_consistent_json(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'Unauthorized')
            ->assertJsonStructure([
                'error' => ['type', 'message', 'details'],
            ]);
    }

    public function test_authorization_exception_returns_consistent_json(): void
    {
        [$user] = $this->professionalWithProfile();
        [, $otherProfile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $otherProfile->id,
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson("/api/v1/services/{$service->id}");

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'Forbidden')
            ->assertJsonStructure([
                'error' => ['type', 'message', 'details'],
            ]);
    }

    public function test_model_not_found_exception_returns_consistent_json(): void
    {
        [$user] = $this->professionalWithProfile();
        $missingServiceId = (string) Str::uuid();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson("/api/v1/services/{$missingServiceId}");

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'NotFound')
            ->assertJsonStructure([
                'error' => ['type', 'message', 'details'],
            ]);
    }

    public function test_custom_api_exception_returns_consistent_json(): void
    {
        $user = User::factory()->professional()->create();

        $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/professional-profile', [
                'bio' => 'First profile.',
            ])
            ->assertCreated();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/professional-profile', [
                'bio' => 'Duplicate profile.',
            ]);

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'ProfessionalProfileAlreadyExists')
            ->assertJsonStructure([
                'error' => ['type', 'message', 'details'],
            ]);
    }

    private function authHeaders(User $user): array
    {
        $token = auth('user_jwt')->login($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    private function professionalWithProfile(): array
    {
        $user = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        return [$user, $profile];
    }
}

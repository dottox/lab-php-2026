<?php

namespace Tests\Feature\Service;

use App\Models\Service\Service;
use App\Models\User\ProfessionalProfile;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_services_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/v1/services', $this->servicePayload());

        $response
            ->assertUnauthorized()
            ->assertJsonPath('error.type', 'Unauthorized');
    }

    public function test_post_services_without_professional_profile_returns_403(): void
    {
        $user = User::factory()->professional()->create();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/services', $this->servicePayload());

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'ProfessionalProfileRequired');
    }

    public function test_post_services_creates_service_for_authenticated_professional(): void
    {
        [$user, $profile] = $this->professionalWithProfile();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/services', $this->servicePayload([
                'name' => 'Consulting session',
            ]));

        $response
            ->assertCreated()
            ->assertJsonPath('service.name', 'Consulting session')
            ->assertJsonPath('service.professional_id', $profile->id)
            ->assertJsonStructure([
                'message',
                'service' => [
                    'id',
                    'professional_id',
                    'company_id',
                    'name',
                    'description',
                    'price',
                    'duration_minutes',
                    'modality',
                    'is_active',
                ],
            ]);

        $this->assertDatabaseHas('services', [
            'professional_id' => $profile->id,
            'name' => 'Consulting session',
        ]);
    }

    public function test_post_services_validates_required_fields(): void
    {
        [$user] = $this->professionalWithProfile();
        $payload = $this->servicePayload();

        unset(
            $payload['name'],
            $payload['price'],
            $payload['duration_minutes'],
            $payload['modality']
        );

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/services', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error.type', 'ValidationError')
            ->assertJsonStructure([
                'error' => [
                    'details' => ['name', 'price', 'duration_minutes', 'modality'],
                ],
            ]);
    }

    public function test_post_services_validates_modality(): void
    {
        [$user] = $this->professionalWithProfile();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/services', $this->servicePayload([
                'modality' => 'onsite',
            ]));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error.type', 'ValidationError')
            ->assertJsonStructure([
                'error' => [
                    'details' => ['modality'],
                ],
            ]);
    }

    public function test_post_services_validates_duration_minutes(): void
    {
        [$user] = $this->professionalWithProfile();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/services', $this->servicePayload([
                'duration_minutes' => 20,
            ]));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error.type', 'ValidationError')
            ->assertJsonStructure([
                'error' => [
                    'details' => ['duration_minutes'],
                ],
            ]);
    }

    public function test_get_my_services_returns_only_authenticated_professionals_services(): void
    {
        [$user, $profile] = $this->professionalWithProfile();
        [, $otherProfile] = $this->professionalWithProfile();

        $myService = Service::factory()->create([
            'professional_id' => $profile->id,
            'name' => 'Mine',
        ]);
        $otherService = Service::factory()->create([
            'professional_id' => $otherProfile->id,
            'name' => 'Other',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/services/my');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'services')
            ->assertJsonPath('services.0.id', $myService->id);

        $this->assertNotSame($otherService->id, $response->json('services.0.id'));
    }

    public function test_get_service_allows_owner_to_view_service(): void
    {
        [$user, $profile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $profile->id,
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson("/api/v1/services/{$service->id}");

        $response
            ->assertOk()
            ->assertJsonPath('service.id', $service->id)
            ->assertJsonPath('service.professional_id', $profile->id);
    }

    public function test_get_service_blocks_foreign_service_with_403(): void
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
            ->assertJsonPath('error.type', 'Forbidden');
    }

    public function test_put_service_allows_owner_to_update_service(): void
    {
        [$user, $profile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $profile->id,
            'name' => 'Original service',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson("/api/v1/services/{$service->id}", [
                'name' => 'Updated service',
                'price' => 250,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('service.id', $service->id)
            ->assertJsonPath('service.name', 'Updated service');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated service',
        ]);
    }

    public function test_put_service_blocks_foreign_service_with_403(): void
    {
        [$user] = $this->professionalWithProfile();
        [, $otherProfile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $otherProfile->id,
            'name' => 'Foreign service',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson("/api/v1/services/{$service->id}", [
                'name' => 'Should not update',
            ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('error.type', 'Forbidden');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Foreign service',
        ]);
    }

    public function test_delete_service_allows_owner_to_soft_delete_service(): void
    {
        [$user, $profile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $profile->id,
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->deleteJson("/api/v1/services/{$service->id}");

        $response
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }

    public function test_delete_service_blocks_foreign_service_with_403(): void
    {
        [$user] = $this->professionalWithProfile();
        [, $otherProfile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $otherProfile->id,
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->deleteJson("/api/v1/services/{$service->id}");

        $response
            ->assertForbidden()
            ->assertJsonPath('error.type', 'Forbidden');

        $this->assertNotSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }

    public function test_delete_service_does_not_physically_delete_record(): void
    {
        [$user, $profile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $profile->id,
        ]);

        $this
            ->withHeaders($this->authHeaders($user))
            ->deleteJson("/api/v1/services/{$service->id}")
            ->assertOk();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
        ]);
        $this->assertSoftDeleted('services', [
            'id' => $service->id,
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

    private function servicePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Strategy consultation',
            'description' => 'A focused professional service.',
            'price' => 150,
            'duration_minutes' => 60,
            'modality' => 'remota',
            'address' => null,
            'link' => 'https://example.test/meeting',
            'latitude' => null,
            'longitude' => null,
            'max_bookings_per_client' => null,
            'min_reschedule_minutes' => 30,
            'buffer_minutes' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ], $overrides);
    }
}

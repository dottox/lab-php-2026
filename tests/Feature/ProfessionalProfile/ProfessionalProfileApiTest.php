<?php

namespace Tests\Feature\ProfessionalProfile;

use App\Models\User\ProfessionalProfile;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_professional_profile_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/v1/professional-profile', [
            'bio' => 'Independent consultant.',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('error.type', 'Unauthorized');
    }

    public function test_post_creates_professional_profile(): void
    {
        $user = User::factory()->professional()->create();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/professional-profile', [
                'bio' => 'Backend consultant.',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Professional profile created successfully')
            ->assertJsonPath('professional_profile.bio', 'Backend consultant.')
            ->assertJsonStructure([
                'professional_profile' => ['id', 'bio', 'avg_rating', 'reviews_count', 'is_verified'],
            ]);

        $this->assertDatabaseHas('professional_profiles', [
            'user_id' => $user->id,
            'bio' => 'Backend consultant.',
        ]);
    }

    public function test_post_twice_returns_409(): void
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
                'bio' => 'Second profile.',
            ]);

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.type', 'ProfessionalProfileAlreadyExists');
    }

    public function test_get_returns_my_professional_profile(): void
    {
        $user = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Profile for the authenticated user.',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/professional-profile');

        $response
            ->assertOk()
            ->assertJsonPath('professional_profile.id', $profile->id)
            ->assertJsonPath('professional_profile.bio', $profile->bio);
    }

    public function test_get_without_profile_returns_404(): void
    {
        $user = User::factory()->professional()->create();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/professional-profile');

        $response
            ->assertNotFound()
            ->assertJsonPath('message', 'Professional profile not found');
    }

    public function test_put_updates_bio(): void
    {
        $user = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Old bio.',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/professional-profile', [
                'bio' => 'Updated bio.',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Professional profile updated successfully')
            ->assertJsonPath('professional_profile.id', $profile->id)
            ->assertJsonPath('professional_profile.bio', 'Updated bio.');

        $this->assertDatabaseHas('professional_profiles', [
            'id' => $profile->id,
            'bio' => 'Updated bio.',
        ]);
    }

    public function test_put_without_profile_returns_404(): void
    {
        $user = User::factory()->professional()->create();

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/professional-profile', [
                'bio' => 'No profile yet.',
            ]);

        $response
            ->assertNotFound()
            ->assertJsonPath('error.type', 'NotFound');
    }

    public function test_current_user_endpoint_does_not_return_another_users_profile(): void
    {
        $user = User::factory()->professional()->create();
        $otherUser = User::factory()->professional()->create();

        $myProfile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Mine.',
        ]);
        $otherProfile = ProfessionalProfile::factory()->create([
            'user_id' => $otherUser->id,
            'bio' => 'Other.',
        ]);

        $response = $this
            ->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/professional-profile');

        $response
            ->assertOk()
            ->assertJsonPath('professional_profile.id', $myProfile->id);

        $this->assertNotSame($otherProfile->id, $response->json('professional_profile.id'));
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

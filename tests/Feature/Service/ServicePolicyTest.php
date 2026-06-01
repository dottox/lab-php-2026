<?php

namespace Tests\Feature\Service;

use App\Models\Service\Service;
use App\Models\User\ProfessionalProfile;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ServicePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_update_and_delete_own_service(): void
    {
        [$user, $profile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $profile->id,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $service));
        $this->assertTrue(Gate::forUser($user)->allows('update', $service));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $service));
    }

    public function test_non_owner_cannot_view_update_or_delete_service(): void
    {
        [$user] = $this->professionalWithProfile();
        [, $otherProfile] = $this->professionalWithProfile();
        $service = Service::factory()->create([
            'professional_id' => $otherProfile->id,
        ]);

        $this->assertFalse(Gate::forUser($user)->allows('view', $service));
        $this->assertFalse(Gate::forUser($user)->allows('update', $service));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $service));
    }

    public function test_user_without_professional_profile_cannot_view_update_or_delete_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('view', $service));
        $this->assertFalse(Gate::forUser($user)->allows('update', $service));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $service));
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

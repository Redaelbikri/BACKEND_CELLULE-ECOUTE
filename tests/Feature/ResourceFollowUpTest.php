<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Appointment;
use App\Models\EducationalResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_educational_resources(): void
    {
        $admin = User::factory()->create(['role' => RoleEnum::ADMIN->value]);

        $response = $this->withTokenFor($admin)->postJson('/api/admin/resources', [
            'title' => 'Guide de respiration',
            'category' => 'Bien-etre',
            'type' => 'guide',
            'description' => 'Un guide court pour respirer calmement.',
            'content' => 'Inspirez, expirez, recommencez.',
            'reading_time' => 3,
            'is_published' => true,
        ]);

        $response->assertCreated()->assertJsonPath('data.title', 'Guide de respiration');
        $id = $response->json('data.id');

        $this->withTokenFor($admin)
            ->putJson("/api/admin/resources/$id", ['title' => 'Guide de respiration calme'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Guide de respiration calme');

        $this->withTokenFor($admin)
            ->patchJson("/api/admin/resources/$id/unpublish")
            ->assertOk()
            ->assertJsonPath('data.is_published', false);
    }

    public function test_student_can_use_resources_mood_and_goals(): void
    {
        $student = User::factory()->create(['role' => RoleEnum::STUDENT->value]);
        $resource = $this->publishedResource();

        $this->withTokenFor($student)
            ->getJson('/api/student/resources')
            ->assertOk()
            ->assertJsonPath('data.0.id', $resource->id);

        $this->withTokenFor($student)
            ->postJson("/api/student/resources/{$resource->id}/save")
            ->assertOk();

        $this->withTokenFor($student)
            ->getJson('/api/student/saved-resources')
            ->assertOk()
            ->assertJsonPath('data.0.id', $resource->id);

        $this->withTokenFor($student)
            ->postJson('/api/student/mood-journal', ['mood' => 'bien', 'note' => 'Journee stable'])
            ->assertCreated()
            ->assertJsonPath('data.mood', 'bien');

        $goalResponse = $this->withTokenFor($student)
            ->postJson('/api/student/goals', [
                'title' => 'Reviser deux heures',
                'category' => 'academique',
            ])
            ->assertCreated();

        $this->withTokenFor($student)
            ->patchJson('/api/student/goals/'.$goalResponse->json('data.id').'/status', ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_counselor_can_recommend_and_create_follow_up_for_followed_student(): void
    {
        $student = User::factory()->create(['role' => RoleEnum::STUDENT->value]);
        $counselor = User::factory()->create(['role' => RoleEnum::COUNSELOR->value]);
        $resource = $this->publishedResource();
        $appointment = Appointment::query()->create([
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'appointment_date' => now()->subDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'type' => 'psychique',
            'reason' => 'Besoin de suivi',
            'status' => AppointmentStatusEnum::COMPLETED->value,
        ]);

        $this->withTokenFor($counselor)
            ->postJson("/api/counselor/students/{$student->id}/recommend-resource", [
                'resource_id' => $resource->id,
                'note' => 'A lire avant le prochain rendez-vous.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.resource.id', $resource->id);

        $this->withTokenFor($counselor)
            ->getJson("/api/counselor/students/{$student->id}/recommended-resources")
            ->assertOk()
            ->assertJsonPath('data.0.resource.id', $resource->id);

        $this->withTokenFor($counselor)
            ->postJson("/api/counselor/students/{$student->id}/follow-up-plans", [
                'appointment_id' => $appointment->id,
                'title' => 'Plan de reprise',
                'objective' => 'Stabiliser la semaine de travail.',
                'actions' => 'Planifier trois sessions courtes.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Plan de reprise');
    }

    private function withTokenFor(User $user)
    {
        $token = app(AuthService::class)->issueToken($user)['access_token'];

        return $this->withHeader('Authorization', 'Bearer '.$token);
    }

    private function publishedResource(): EducationalResource
    {
        return EducationalResource::query()->create([
            'title' => 'Respiration simple',
            'slug' => 'respiration-simple',
            'category' => 'Bien-etre',
            'type' => 'exercice',
            'description' => 'Un exercice rapide.',
            'content' => 'Respirez pendant trois minutes.',
            'reading_time' => 2,
            'is_published' => true,
        ]);
    }
}

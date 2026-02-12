<?php

namespace Tests\Feature;

use App\Jobs\GenerateExport;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_export(): void
    {
        $this->post(route('export.store'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_an_export_request(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('export.store'), [
                'include_images' => true,
                'include_results' => true,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'export' => ['id', 'status', 'file_size_bytes', 'download_url', 'created_at']
            ]);

        $this->assertDatabaseHas('exports', [
            'user_id' => $user->id,
            'include_images' => true,
            'include_results' => true,
        ]);
    }

    public function test_user_can_check_export_status(): void
    {
        $user = User::factory()->create();
        $export = Export::factory()->for($user)->create(['status' => 'pending']);

        $this->actingAs($user)
            ->getJson(route('export.status', $export))
            ->assertOk()
            ->assertJson([
                'export' => [
                    'status' => 'pending',
                ],
            ]);
    }

    public function test_user_cannot_access_other_users_export(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $export = Export::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->getJson(route('export.status', $export))
            ->assertForbidden();
    }

    public function test_export_creation_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('export.store'), [
                'include_images' => true,
                'include_results' => true,
            ])
            ->assertOk();

        Queue::assertPushed(GenerateExport::class);
    }

    public function test_download_completed_export(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/test-export.zip', 'fake zip content');

        $user = User::factory()->create();
        $export = Export::factory()->for($user)->create([
            'status' => 'completed',
            'file_path' => 'exports/test-export.zip',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->get(route('export.download', $export))
            ->assertOk();
    }

    public function test_download_non_completed_export_fails(): void
    {
        $user = User::factory()->create();
        $export = Export::factory()->for($user)->create([
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('export.download', $export))
            ->assertNotFound();
    }

    public function test_download_expired_export_fails(): void
    {
        $user = User::factory()->create();
        $export = Export::factory()->for($user)->create([
            'status' => 'completed',
            'file_path' => 'exports/test.zip',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('export.download', $export))
            ->assertNotFound();
    }

    public function test_concurrent_export_returns_existing(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $existingExport = Export::factory()->for($user)->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('export.store'), [
                'include_images' => true,
            ])
            ->assertOk();

        // Should return existing export, not create a new one
        $response->assertJson([
            'export' => [
                'id' => $existingExport->id,
                'status' => 'pending',
            ],
        ]);

        // Job should NOT be dispatched for duplicate
        Queue::assertNotPushed(GenerateExport::class);
    }

    public function test_export_status_includes_expected_structure(): void
    {
        $user = User::factory()->create();
        $export = Export::factory()->completed()->for($user)->create();

        $this->actingAs($user)
            ->getJson(route('export.status', $export))
            ->assertOk()
            ->assertJsonStructure([
                'export' => ['id', 'status', 'file_size_bytes', 'download_url', 'created_at'],
            ]);
    }

    public function test_user_cannot_download_other_users_export(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $export = Export::factory()->completed()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('export.download', $export))
            ->assertForbidden();
    }
}

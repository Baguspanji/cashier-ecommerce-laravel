<?php

namespace Tests\Feature\Api;

use App\Models\SyncLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_sync_status_endpoint(): void
    {
        // Create some sync logs
        SyncLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        SyncLog::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'synced_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/sync/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'pending_operations',
                    'last_sync_at',
                    'sync_required',
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pending_operations' => 3,
                    'sync_required' => true,
                ]
            ]);
    }

    public function test_pending_sync_operations(): void
    {
        // Create pending sync logs
        $syncLog = SyncLog::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'operation' => 'create',
            'table_name' => 'transactions',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/sync/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'operation',
                        'table_name',
                        'record_id',
                        'offline_id',
                        'data',
                        'created_at',
                    ]
                ]
            ]);
    }

    public function test_mark_sync_completed(): void
    {
        $syncLog = SyncLog::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sync/complete', [
                'sync_ids' => [$syncLog->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sync operations marked as completed',
            ]);

        $syncLog->refresh();
        $this->assertEquals('completed', $syncLog->status);
        $this->assertNotNull($syncLog->synced_at);
    }

    public function test_log_sync_failure(): void
    {
        $syncLog = SyncLog::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sync/log-failure', [
                'sync_id' => $syncLog->id,
                'error_message' => 'Connection timeout',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sync failure logged',
            ]);

        $syncLog->refresh();
        $this->assertEquals('failed', $syncLog->status);
        $this->assertEquals('Connection timeout', $syncLog->error_message);
    }

    public function test_unauthorized_access_denied(): void
    {
        $response = $this->getJson('/api/sync/status');
        $response->assertStatus(401);
    }
}

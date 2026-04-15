<?php

namespace Tests\Feature\Api;

use App\Enums\TaskStatus;
use App\Events\TaskUpdated;
use App\Listeners\SendNonComplianceNotification;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskNonCompliantNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_task_lifecycle(): void
    {
        $user = User::factory()->create();

        $res = $this->postJson('/api/tasks', [
            'title'    => 'Check emergency exits',
            'due_date' => now()->addDays(5)->toDateString(),
            'user_id'  => $user->id,
            'priority' => 'high',
        ]);

        $res->assertCreated();
        $taskId = $res->json('data.id');

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Check emergency exits']);

        $this->getJson("/api/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('data.priority', 'high');

        Event::fake([TaskUpdated::class]);

        $this->putJson("/api/tasks/{$taskId}", [
            'title'    => 'Check emergency exits (updated)',
            'due_date' => now()->addDays(7)->toDateString(),
            'user_id'  => $user->id,
            'priority' => 'medium',
            'status'   => 'completed',
        ])->assertOk();

        Event::assertDispatched(TaskUpdated::class);
        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'status' => 'completed']);
    }

    public function test_marking_non_compliant_without_corrective_action_fails(): void
    {
        $task = Task::factory()->create(['status' => TaskStatus::Pending]);

        $this->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'non_compliant',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors('corrective_action');
    }

    public function test_marking_non_compliant_with_corrective_action_works(): void
    {
        $task = Task::factory()->create(['status' => TaskStatus::Pending]);

        $this->patchJson("/api/tasks/{$task->id}/status", [
            'status'            => 'non_compliant',
            'corrective_action' => 'Replaced broken sensor on floor 2.',
        ])->assertOk();

        $task->refresh();
        $this->assertEquals(TaskStatus::NonCompliant, $task->status);
        $this->assertEquals('Replaced broken sensor on floor 2.', $task->corrective_action);
    }

    public function test_filters_narrow_results(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::Pending]);
        Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::Completed]);
        Task::factory()->create(); // different user

        $this->getJson('/api/tasks?status=pending')
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/tasks?user_id={$user->id}")
            ->assertJsonCount(2, 'data');
    }

    public function test_non_compliance_triggers_notification(): void
    {
        Notification::fake();

        $task = Task::factory()->create(['status' => TaskStatus::Pending]);
        $task->update(['status' => TaskStatus::NonCompliant, 'corrective_action' => 'Fix it.']);

        (new SendNonComplianceNotification)->handle(new TaskUpdated($task));

        Notification::assertSentTo($task->user, TaskNonCompliantNotification::class);
    }

    public function test_notification_skipped_when_status_did_not_change(): void
    {
        Notification::fake();

        $task = Task::factory()->create([
            'status'            => TaskStatus::NonCompliant,
            'corrective_action' => 'Already noted.',
        ]);
        $task->update(['title' => 'Renamed task']);

        (new SendNonComplianceNotification)->handle(new TaskUpdated($task));

        Notification::assertNothingSent();
    }
}

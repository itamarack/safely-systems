<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskNonCompliantNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNonComplianceNotification implements ShouldQueue
{
    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;

        if (!$task->wasChanged('status') || !$task->isNonCompliant()) {
            return;
        }

        $task->user->notify(new TaskNonCompliantNotification($task));
    }
}

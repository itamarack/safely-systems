<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskNonCompliantNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Task Non-Compliant: {$this->task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The following task has been marked as non-compliant:")
            ->line("**{$this->task->title}**")
            ->line("Corrective action: {$this->task->corrective_action}")
            ->action('View Task', url(route('tasks.show', $this->task)))
            ->line('Please review and take appropriate action.');
    }
}

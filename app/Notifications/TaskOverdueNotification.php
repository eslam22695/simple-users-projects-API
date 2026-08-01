<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task Overdue: '.$this->task->title)
            ->line("Your task \"{$this->task->title}\" is now overdue.")
            ->line('Due date: '.$this->task->due_date?->format('Y-m-d'))
            ->action('View Task', url("/projects/{$this->task->project_id}/tasks/{$this->task->id}"))
            ->line('Please take action as soon as possible.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'title' => $this->task->title,
            'due_date' => $this->task->due_date?->toIso8601String(),
            'message' => "Task \"{$this->task->title}\" is overdue.",
        ];
    }
}

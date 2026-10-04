<?php

namespace App\Notifications;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly TaskStatus $previousStatus,
        private readonly TaskStatus $newStatus,
    ) {
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Actualización de tarea')
            ->greeting("Hola {$notifiable->name},")
            ->line("La tarea \"{$this->task->title}\" cambió de {$this->previousStatus->value} a {$this->newStatus->value}.")
            ->action('Ver tarea', config('app.url')."/tasks/{$this->task->id}");
    }
}

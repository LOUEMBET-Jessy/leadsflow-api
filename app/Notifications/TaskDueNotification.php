<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Task $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tâche due aujourd\'hui - ' . $this->task->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous avez une tâche due aujourd\'hui :')
            ->line('**Titre :** ' . $this->task->title)
            ->line('**Description :** ' . $this->task->description)
            ->line('**Priorité :** ' . $this->task->priority)
            ->line('**Date d\'échéance :** ' . $this->task->due_date->format('d/m/Y H:i'))
            ->action('Voir la tâche', url('/tasks/' . $this->task->id))
            ->line('Merci d\'utiliser LeadFlow !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'task_priority' => $this->task->priority,
            'due_date' => $this->task->due_date->format('d/m/Y H:i'),
            'message' => 'Tâche due aujourd\'hui : ' . $this->task->title,
        ];
    }
}

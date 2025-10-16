<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Lead $lead;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lead $lead, string $oldStatus, string $newStatus)
    {
        $this->lead = $lead;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
            ->subject('Statut du lead modifié - ' . $this->lead->full_name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Le statut du lead a été modifié :')
            ->line('**Lead :** ' . $this->lead->full_name)
            ->line('**Ancien statut :** ' . $this->oldStatus)
            ->line('**Nouveau statut :** ' . $this->newStatus)
            ->action('Voir le lead', url('/leads/' . $this->lead->id))
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
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->full_name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => 'Le statut du lead ' . $this->lead->full_name . ' a été modifié de ' . $this->oldStatus . ' vers ' . $this->newStatus,
        ];
    }
}

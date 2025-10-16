<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Lead $lead;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
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
            ->subject('Nouveau lead assigné - ' . $this->lead->full_name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un nouveau lead vous a été assigné :')
            ->line('**Nom :** ' . $this->lead->full_name)
            ->line('**Entreprise :** ' . $this->lead->company)
            ->line('**Email :** ' . $this->lead->email)
            ->line('**Priorité :** ' . $this->lead->priority)
            ->line('**Score :** ' . $this->lead->score)
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
            'lead_company' => $this->lead->company,
            'lead_priority' => $this->lead->priority,
            'lead_score' => $this->lead->score,
            'message' => 'Un nouveau lead vous a été assigné : ' . $this->lead->full_name,
        ];
    }
}

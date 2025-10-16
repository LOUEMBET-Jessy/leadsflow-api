<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\LeadAssignedNotification;
use App\Notifications\LeadStatusChangedNotification;
use App\Notifications\TaskDueNotification;
use App\Notifications\TaskOverdueNotification;
use App\Notifications\NewMessageNotification;
use App\Notifications\WeeklySummaryNotification;
use App\Notifications\MonthlyReportNotification;

class NotificationService
{
    /**
     * Send lead assigned notification
     */
    public function sendLeadAssignedNotification(Lead $lead, User $user): void
    {
        if ($this->shouldSendNotification($user, 'lead_assigned')) {
            $user->notify(new LeadAssignedNotification($lead));
        }
    }

    /**
     * Send lead status changed notification
     */
    public function sendLeadStatusChangedNotification(Lead $lead, User $user, string $oldStatus, string $newStatus): void
    {
        if ($this->shouldSendNotification($user, 'lead_status_changed')) {
            $user->notify(new LeadStatusChangedNotification($lead, $oldStatus, $newStatus));
        }
    }

    /**
     * Send task due notification
     */
    public function sendTaskDueNotification(Task $task): void
    {
        if ($this->shouldSendNotification($task->assignedTo, 'task_due')) {
            $task->assignedTo->notify(new TaskDueNotification($task));
        }
    }

    /**
     * Send task overdue notification
     */
    public function sendTaskOverdueNotification(Task $task): void
    {
        if ($this->shouldSendNotification($task->assignedTo, 'task_overdue')) {
            $task->assignedTo->notify(new TaskOverdueNotification($task));
        }
    }

    /**
     * Send new message notification
     */
    public function sendNewMessageNotification(User $user, string $message, string $from): void
    {
        if ($this->shouldSendNotification($user, 'new_message')) {
            $user->notify(new NewMessageNotification($message, $from));
        }
    }

    /**
     * Send weekly summary notification
     */
    public function sendWeeklySummaryNotification(User $user, array $summary): void
    {
        if ($this->shouldSendNotification($user, 'weekly_summary')) {
            $user->notify(new WeeklySummaryNotification($summary));
        }
    }

    /**
     * Send monthly report notification
     */
    public function sendMonthlyReportNotification(User $user, array $report): void
    {
        if ($this->shouldSendNotification($user, 'monthly_report')) {
            $user->notify(new MonthlyReportNotification($report));
        }
    }

    /**
     * Send bulk notification to multiple users
     */
    public function sendBulkNotification(array $users, string $type, array $data): void
    {
        foreach ($users as $user) {
            $this->sendNotificationByType($user, $type, $data);
        }
    }

    /**
     * Send notification by type
     */
    protected function sendNotificationByType(User $user, string $type, array $data): void
    {
        switch ($type) {
            case 'lead_assigned':
                $this->sendLeadAssignedNotification($data['lead'], $user);
                break;
            case 'lead_status_changed':
                $this->sendLeadStatusChangedNotification($data['lead'], $user, $data['old_status'], $data['new_status']);
                break;
            case 'task_due':
                $this->sendTaskDueNotification($data['task']);
                break;
            case 'task_overdue':
                $this->sendTaskOverdueNotification($data['task']);
                break;
            case 'new_message':
                $this->sendNewMessageNotification($user, $data['message'], $data['from']);
                break;
            case 'weekly_summary':
                $this->sendWeeklySummaryNotification($user, $data['summary']);
                break;
            case 'monthly_report':
                $this->sendMonthlyReportNotification($user, $data['report']);
                break;
        }
    }

    /**
     * Check if user should receive notification
     */
    protected function shouldSendNotification(User $user, string $type): bool
    {
        $settings = $user->settings['notifications'] ?? [];
        
        // Check if email notifications are enabled
        if (!($settings['email_notifications'] ?? true)) {
            return false;
        }
        
        // Check specific notification type
        return $settings[$type] ?? true;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Get notification statistics for user
     */
    public function getNotificationStats(User $user): array
    {
        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
        ];
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Send real-time notification via WebSocket
     */
    public function sendRealtimeNotification(User $user, string $type, array $data): void
    {
        // This would integrate with Pusher, Socket.io, or similar
        // For now, we'll just log it
        \Log::info('Real-time notification', [
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data
        ]);
    }

    /**
     * Schedule notification for later
     */
    public function scheduleNotification(User $user, string $type, array $data, \DateTime $sendAt): void
    {
        // This would integrate with Laravel's task scheduler or a queue
        \Log::info('Scheduled notification', [
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
            'send_at' => $sendAt->format('Y-m-d H:i:s')
        ]);
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FileProcessed extends Notification
{
    use Queueable;

    protected int $project_id;
    protected int $user_id;
    protected string $message;
    protected string $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $status, int $project_id, int $user_id)
    {
        $this->project_id = $project_id;
        $this->user_id = $user_id;
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'status' => $this->status,
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->toArray($notifiable)));
    }

    public function databaseType(): string
    {
        return 'file_processed';
    }

    public function initialDatabaseReadAtValue(): ?Carbon
    {
        return null;
    }

    public function broadcastType(): string
    {
        return 'file_processed';
    }
}

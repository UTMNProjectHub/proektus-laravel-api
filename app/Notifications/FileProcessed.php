<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FileProcessed extends Notification
{
    use Queueable;

    protected $project_id;
    protected $user_id;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, int $project_id, int $user_id)
    {
        $this->project_id = $project_id;
        $this->user_id = $user_id;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
        ];
    }

    public function databaseType(): string
    {
        return 'file_processed';
    }

    public function initialDatabaseReadAtValue(): ?Carbon
    {
        return null;
    }
}

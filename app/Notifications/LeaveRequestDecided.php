<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestDecided extends Notification
{
    public function __construct(
        public LeaveRequest $leave,
        public string $decision,
        public User $decidedBy,
        public ?string $comment = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function summary(): string
    {
        return sprintf(
            'Your %s leave (%s - %s) was %s by %s.',
            strtolower($this->leave->type->name),
            $this->leave->start_date->format('M j'),
            $this->leave->end_date->format('M j, Y'),
            $this->decision,
            $this->decidedBy->name
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Leave request '.$this->decision)
            ->greeting("Hello {$notifiable->name},")
            ->line($this->summary());

        if ($this->comment) {
            $mail->line('Comment: '.$this->comment);
        }

        return $mail->action('View my requests', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->summary().($this->comment ? " \"{$this->comment}\"" : ''),
            'url' => route('dashboard'),
        ];
    }
}

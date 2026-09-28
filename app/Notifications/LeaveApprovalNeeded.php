<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApprovalNeeded extends Notification
{
    public function __construct(public LeaveRequest $leave) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function summary(): string
    {
        return sprintf(
            '%s requested %d day(s) of %s leave (%s - %s).',
            $this->leave->user->name,
            $this->leave->days,
            strtolower($this->leave->type->name),
            $this->leave->start_date->format('M j'),
            $this->leave->end_date->format('M j, Y')
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Leave request awaiting your approval')
            ->greeting("Hello {$notifiable->name},")
            ->line($this->summary())
            ->action('Review request', route('approvals.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Approval needed: '.$this->summary(),
            'url' => route('approvals.index'),
        ];
    }
}

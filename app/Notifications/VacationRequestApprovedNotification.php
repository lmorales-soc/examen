<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestApprovedNotification extends Notification
{

    /**
     * @param array{id?: int, employee_id?: int, start_date?: string, end_date?: string, days_requested?: int} $vacationRequest
     */
    public function __construct(
        private readonly array $vacationRequest
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $start = $this->vacationRequest['start_date'] ?? '—';
        $end = $this->vacationRequest['end_date'] ?? '—';
        $days = $this->vacationRequest['days_requested'] ?? 0;
        $url = route('vacation-requests.index');

        return (new MailMessage)
            ->subject('Tu solicitud de vacaciones fue aprobada')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Tu solicitud de vacaciones ha sido **aprobada**.')
            ->line('**Periodo:** del ' . $start . ' al ' . $end . ' (' . $days . ' días).')
            ->action('Ver mis solicitudes', $url)
            ->line('Gracias por usar nuestro sistema de vacaciones.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vacation_approved',
            'message' => 'Tu solicitud de vacaciones fue aprobada.',
            'vacation_request_id' => $this->vacationRequest['id'] ?? null,
            'employee_id' => $this->vacationRequest['employee_id'] ?? null,
            'start_date' => $this->vacationRequest['start_date'] ?? null,
            'end_date' => $this->vacationRequest['end_date'] ?? null,
            'days_requested' => $this->vacationRequest['days_requested'] ?? null,
            'url' => route('vacation-requests.index'),
        ];
    }
}

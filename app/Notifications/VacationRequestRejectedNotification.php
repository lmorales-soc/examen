<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestRejectedNotification extends Notification
{

    /**
     * @param array{id?: int, employee_id?: int, start_date?: string, end_date?: string, days_requested?: int, rejection_reason?: string|null} $vacationRequest
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
        $reason = $this->vacationRequest['rejection_reason'] ?? 'No indicada';
        $url = route('vacation-requests.index');

        $mail = (new MailMessage)
            ->subject('Tu solicitud de vacaciones fue rechazada')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Tu solicitud de vacaciones ha sido **rechazada**.')
            ->line('**Periodo solicitado:** del ' . $start . ' al ' . $end . '.')
            ->line('**Motivo:** ' . $reason)
            ->action('Ver mis solicitudes', $url)
            ->line('Puedes crear una nueva solicitud con otras fechas si lo deseas.');

        return $mail;
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vacation_rejected',
            'message' => 'Tu solicitud de vacaciones fue rechazada.',
            'vacation_request_id' => $this->vacationRequest['id'] ?? null,
            'employee_id' => $this->vacationRequest['employee_id'] ?? null,
            'start_date' => $this->vacationRequest['start_date'] ?? null,
            'end_date' => $this->vacationRequest['end_date'] ?? null,
            'days_requested' => $this->vacationRequest['days_requested'] ?? null,
            'rejection_reason' => $this->vacationRequest['rejection_reason'] ?? null,
            'url' => route('vacation-requests.index'),
        ];
    }
}

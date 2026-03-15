<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array{id?: int, employee_id?: int, employee_name?: string, start_date?: string, end_date?: string, days_requested?: int} $vacationRequest
     */
    public function __construct(
        private readonly array $vacationRequest
    ) {
    }

    /**
     * Canales: correo y notificación en base de datos.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = $this->vacationRequest['employee_name'] ?? 'Un empleado';
        $start = $this->vacationRequest['start_date'] ?? '—';
        $end = $this->vacationRequest['end_date'] ?? '—';
        $days = $this->vacationRequest['days_requested'] ?? 0;
        $requestId = $this->vacationRequest['id'] ?? null;

        $url = $requestId
            ? route('approvals.index')
            : url('/');

        return (new MailMessage)
            ->subject('Nueva solicitud de vacaciones pendiente de aprobación')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Se ha creado una nueva solicitud de vacaciones que requiere tu aprobación.')
            ->line('**Solicitante:** ' . $employeeName)
            ->line('**Periodo:** del ' . $start . ' al ' . $end . ' (' . $days . ' días).')
            ->action('Ver aprobaciones pendientes', $url)
            ->line('Gracias por usar nuestro sistema de vacaciones.');
    }

    /**
     * Datos para la notificación interna (tabla notifications).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vacation_request_created',
            'message' => 'Nueva solicitud de vacaciones pendiente de aprobación',
            'vacation_request_id' => $this->vacationRequest['id'] ?? null,
            'employee_id' => $this->vacationRequest['employee_id'] ?? null,
            'employee_name' => $this->vacationRequest['employee_name'] ?? null,
            'start_date' => $this->vacationRequest['start_date'] ?? null,
            'end_date' => $this->vacationRequest['end_date'] ?? null,
            'days_requested' => $this->vacationRequest['days_requested'] ?? null,
            'url' => route('approvals.index'),
        ];
    }
}

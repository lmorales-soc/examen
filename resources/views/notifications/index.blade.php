@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="page-title mb-0">Notificaciones</h1>
        @if(isset($viewAll) && $viewAll)
            <span class="badge bg-secondary">Viendo todas (Admin/RH)</span>
        @endif
        @if(!isset($viewAll) || !$viewAll)
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">Marcar todas como leídas</button>
                </form>
            @endif
        @endif
    </div>

    <div class="card">
        <div class="card-body p-0">
            @forelse($notifications as $n)
                @php
                    $type = $n->data['type'] ?? 'vacation_request_created';
                    $statusLabel = match($type) {
                        'vacation_approved' => 'Fue aprobada',
                        'vacation_rejected' => 'Fue rechazada',
                        default => 'Solicitud de vacaciones',
                    };
                    $statusBadgeClass = match($type) {
                        'vacation_approved' => 'bg-success',
                        'vacation_rejected' => 'bg-danger',
                        default => 'bg-info',
                    };
                @endphp
                <div class="border-bottom p-3 {{ $n->read_at ? '' : 'bg-light' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 small text-uppercase text-muted fw-semibold">Solicitud de vacaciones</p>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                @if($n->read_at)
                                    <span class="badge bg-secondary">Leída</span>
                                @else
                                    <span class="badge bg-primary">Nueva</span>
                                @endif
                            </div>
                            <p class="mb-1 {{ $n->read_at ? 'text-muted' : 'fw-medium' }}">
                                {{ $n->data['message'] ?? 'Notificación' }}
                            </p>
                            @if(!empty($n->data['employee_name']) || !empty($n->data['start_date']))
                                <p class="small text-muted mb-0">
                                    @if(!empty($n->data['employee_name']))
                                        {{ $n->data['employee_name'] }}
                                        @if(!empty($n->data['start_date']))
                                            · {{ $n->data['start_date'] }} – {{ $n->data['end_date'] ?? '' }}
                                        @endif
                                    @else
                                        {{ $n->data['start_date'] ?? '' }} – {{ $n->data['end_date'] ?? '' }}
                                    @endif
                                </p>
                            @endif
                            @if(!empty($n->data['rejection_reason']) && $type === 'vacation_rejected')
                                <p class="small mb-0 mt-1"><span class="text-danger">Motivo:</span> {{ $n->data['rejection_reason'] }}</p>
                            @endif
                            @if(!empty($viewAll) && isset($n->notifiable))
                                <p class="small mb-0 mt-1 text-muted">Para: <strong>{{ $n->notifiable->name ?? $n->notifiable->email ?? '—' }}</strong></p>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $isOwn = !isset($viewAll) || !$viewAll || (isset($n->notifiable_id) && $n->notifiable_id == auth()->id());
                            @endphp
                            @if($isOwn)
                                <form action="{{ route('notifications.mark-read', $n->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">{{ $n->read_at ? 'Ver' : 'Ir' }}</button>
                                </form>
                            @else
                                <a href="{{ $n->data['url'] ?? route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            @endif
                        </div>
                    </div>
                    <p class="small text-muted mb-0 mt-1">
                        <span class="text-secondary">{{ $n->created_at->format('d/m/Y H:i') }}</span>
                        <span class="ms-1">({{ $n->created_at->diffForHumans() }})</span>
                    </p>
                </div>
            @empty
                <div class="text-center py-5 px-3">
                    @if(!empty($viewAll))
                        <p class="text-muted mb-0">No hay notificaciones en el sistema.</p>
                    @else
                        <p class="text-muted mb-2">No tienes notificaciones de solicitudes de vacaciones.</p>
                        <p class="small text-muted mb-0">Cuando solicites vacaciones o un gerente apruebe o rechace tu solicitud, verás aquí el historial con la fecha y si fue aprobada o rechazada.</p>
                    @endif
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="card-footer bg-white">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection

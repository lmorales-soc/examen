@extends('layouts.app')

@section('title', 'Lista de solicitudes')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="page-title mb-0">Solicitudes de vacaciones</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('vacation-requests.create') }}" class="btn btn-primary">Solicitar vacaciones</a>
            <a href="{{ route('vacation-requests.calendar') }}" class="btn btn-outline-secondary">Ver calendario</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Empleado</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Días</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $r)
                            @php
                                $employeeName = null;
                                if (!empty($r['employee']['first_name']) || !empty($r['employee']['last_name'])) {
                                    $employeeName = trim(($r['employee']['first_name'] ?? '') . ' ' . ($r['employee']['last_name'] ?? ''));
                                }
                                if ($employeeName === '') {
                                    $employeeName = 'Empleado #' . ($r['employee_id'] ?? '—');
                                }
                            @endphp
                            <tr>
                                <td class="ps-3">{{ $r['id'] ?? '—' }}</td>
                                <td>{{ $employeeName }}</td>
                                <td>{{ isset($r['start_date']) ? date('d/m/Y', strtotime($r['start_date'])) : '—' }}</td>
                                <td>{{ isset($r['end_date']) ? date('d/m/Y', strtotime($r['end_date'])) : '—' }}</td>
                                <td>{{ $r['days_requested'] ?? '—' }}</td>
                                <td>
                                    @php $status = $r['status'] ?? 'pending'; @endphp
                                    @if($status === 'approved')
                                        <span class="badge bg-success">Aprobada</span>
                                    @elseif($status === 'rejected')
                                        <span class="badge bg-danger">Rechazada</span>
                                    @elseif($status === 'cancelled')
                                        <span class="badge bg-secondary">Cancelada</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No hay solicitudes de vacaciones.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Aprobaciones pendientes')

@section('content')
    <h1 class="page-title mb-4">Aprobaciones pendientes</h1>
    <p class="text-muted mb-4">Solicitudes que requieren tu aprobación o rechazo.</p>

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
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $r)
                            <tr>
                                <td class="ps-3">{{ $r['id'] ?? '—' }}</td>
                                <td>{{ $r['employee_id'] ?? '—' }}</td>
                                <td>{{ isset($r['start_date']) ? date('d/m/Y', strtotime($r['start_date'])) : '—' }}</td>
                                <td>{{ isset($r['end_date']) ? date('d/m/Y', strtotime($r['end_date'])) : '—' }}</td>
                                <td>{{ $r['days_requested'] ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('approvals.approve') }}" method="post" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="request_id" value="{{ $r['id'] ?? '' }}">
                                            <button type="submit" class="btn btn-success">Aprobar</button>
                                        </form>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $r['id'] ?? 0 }}">Rechazar</button>
                                    </div>
                                </td>
                            </tr>
                            {{-- Modal rechazo --}}
                            <div class="modal fade" id="rejectModal{{ $r['id'] ?? 0 }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $r['id'] ?? 0 }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('approvals.reject') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="request_id" value="{{ $r['id'] ?? '' }}">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel{{ $r['id'] ?? 0 }}">Rechazar solicitud #{{ $r['id'] ?? '' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label for="rejection_reason_{{ $r['id'] ?? 0 }}" class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                                                <textarea id="rejection_reason_{{ $r['id'] ?? 0 }}" name="rejection_reason" class="form-control" rows="3" required placeholder="Indica el motivo..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger">Rechazar solicitud</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No hay solicitudes pendientes de aprobación.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

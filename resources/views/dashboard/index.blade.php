@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Dashboard</h1>
    </div>
    <p class="text-muted mb-4">Resumen del sistema de gestión de vacaciones.</p>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-primary" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Pendientes de aprobación</h6>
                            <p class="card-title mb-0 fs-4 fw-bold">{{ count($pendingRequests) }}</p>
                        </div>
                    </div>
                    <a href="{{ route('approvals.index') }}" class="btn btn-primary btn-sm mt-3">Ver aprobaciones</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-success" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Solicitudes</h6>
                            <p class="card-title mb-0 fs-4 fw-bold">{{ $totalRequests ?? 0 }}</p>
                        </div>
                    </div>
                    <a href="{{ route('vacation-requests.index') }}" class="btn btn-outline-success btn-sm mt-3">Ver solicitudes</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-info" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Empleados</h6>
                            <p class="card-title mb-0 fs-4 fw-bold">{{ $totalEmployees ?? 0 }}</p>
                        </div>
                    </div>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-info btn-sm mt-3">Ver empleados</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Solicitudes (5 por página)</h5>
        </div>
        <div class="card-body">
            @if($latestRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Días</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestRequests as $r)
                                @php
                                    $emp = $r->employee;
                                    $empName = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '—';
                                    if ($empName === '') {
                                        $empName = '—';
                                    }
                                    $status = $r->status ?? 'pending';
                                @endphp
                                <tr>
                                    <td>{{ $r->id }}</td>
                                    <td>{{ $empName }}</td>
                                    <td>{{ $r->start_date ? $r->start_date->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $r->end_date ? $r->end_date->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $r->days_requested }}</td>
                                    <td>
                                        @if($status === 'approved')
                                            <span class="badge bg-success">Aprobada</span>
                                        @elseif($status === 'rejected')
                                            <span class="badge bg-danger">Rechazada</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $latestRequests->links() }}
                </div>
            @else
                <p class="text-muted mb-0">No hay solicitudes registradas.</p>
            @endif
        </div>
    </div>
@endsection

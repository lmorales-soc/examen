@extends('layouts.app')

@section('title', 'Lista de empleados')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="page-title mb-0">Empleados</h1>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">Nuevo empleado</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nº empleado</th>
                            <th>Nombre</th>
                            <th>Área</th>
                            <th>Fecha alta</th>
                            <th>Días vac.</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $e)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $e['employee_number'] ?? $e['id'] }}</td>
                                <td>{{ trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? '')) ?: '—' }}</td>
                                <td>{{ $e['area_id'] ?? '—' }}</td>
                                <td>{{ isset($e['hire_date']) ? date('d/m/Y', strtotime($e['hire_date'])) : '—' }}</td>
                                <td>{{ $e['vacation_days_annual'] ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('employees.show', $e['id']) }}" class="btn btn-outline-primary">Ver</a>
                                        <a href="{{ route('employees.edit', $e['id']) }}" class="btn btn-outline-secondary">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No hay empleados registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

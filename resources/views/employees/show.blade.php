@extends('layouts.app')

@section('title', 'Ver empleado')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Empleados</a></li>
            <li class="breadcrumb-item active">{{ $employee['employee_number'] ?? $employee['id'] }}</li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="page-title mb-0">Detalle del empleado</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.edit', $employee['id']) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Datos del empleado</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small">Número de empleado</label>
                    <p class="mb-0 fw-medium">{{ $employee['employee_number'] ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Nombre completo</label>
                    <p class="mb-0 fw-medium">{{ trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')) ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Área (ID)</label>
                    <p class="mb-0">{{ $employee['area_id'] ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Fecha de alta</label>
                    <p class="mb-0">{{ isset($employee['hire_date']) ? date('d/m/Y', strtotime($employee['hire_date'])) : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Días de vacaciones anuales</label>
                    <p class="mb-0">{{ $employee['vacation_days_annual'] ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

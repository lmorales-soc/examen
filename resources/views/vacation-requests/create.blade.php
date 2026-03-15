@extends('layouts.app')

@section('title', 'Solicitar vacaciones')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('vacation-requests.index') }}">Solicitudes</a></li>
            <li class="breadcrumb-item active">Nueva solicitud</li>
        </ol>
    </nav>
    <h1 class="page-title mb-4">Solicitar vacaciones</h1>

    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-4">Indica el rango de fechas. Solo se cuentan días hábiles (lun–vie) y el máximo por solicitud es 6 días consecutivos.</p>
            <form action="{{ route('vacation-requests.store') }}" method="post" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee['id'] }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="employee_display" class="form-label">Empleado</label>
                        <input type="text" id="employee_display" class="form-control bg-light" value="{{ trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')) }} (Nº {{ $employee['employee_number'] ?? $employee['id'] }})" readonly disabled>
                        <small class="text-muted">Solicitud a nombre de su usuario. No editable.</small>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Fecha inicio</label>
                        <input type="date" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Fecha fin</label>
                        <input type="date" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="comments" class="form-label">Comentarios (opcional)</label>
                        <textarea id="comments" name="comments" class="form-control @error('comments') is-invalid @enderror" rows="3" placeholder="Motivo o observaciones">{{ old('comments') }}</textarea>
                        @error('comments')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enviar solicitud</button>
                    <a href="{{ route('vacation-requests.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

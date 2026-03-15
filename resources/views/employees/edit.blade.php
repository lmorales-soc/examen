@extends('layouts.app')

@section('title', 'Editar empleado')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Empleados</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employees.show', $employee['id']) }}">{{ $employee['employee_number'] ?? $employee['id'] }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
    <h1 class="page-title mb-4">Editar empleado</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('employees.update', $employee['id']) }}" method="post" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="area_id" class="form-label">Área (ID)</label>
                        <input type="number" id="area_id" name="area_id" class="form-control @error('area_id') is-invalid @enderror" value="{{ old('area_id', $employee['area_id'] ?? '') }}">
                        @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="employee_number" class="form-label">Número de empleado</label>
                        <input type="text" id="employee_number" name="employee_number" class="form-control @error('employee_number') is-invalid @enderror" value="{{ old('employee_number', $employee['employee_number'] ?? '') }}">
                        @error('employee_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">Nombre</label>
                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee['first_name'] ?? '') }}">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Apellidos</label>
                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee['last_name'] ?? '') }}">
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="hire_date" class="form-label">Fecha de alta</label>
                        <input type="date" id="hire_date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror" value="{{ old('hire_date', $employee['hire_date'] ?? '') }}">
                        @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="vacation_days_annual" class="form-label">Días vacaciones anual</label>
                        <input type="number" id="vacation_days_annual" name="vacation_days_annual" class="form-control @error('vacation_days_annual') is-invalid @enderror" value="{{ old('vacation_days_annual', $employee['vacation_days_annual'] ?? '') }}" min="0">
                        @error('vacation_days_annual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('employees.show', $employee['id']) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

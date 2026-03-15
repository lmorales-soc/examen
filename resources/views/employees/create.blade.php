@extends('layouts.app')

@section('title', 'Crear empleado')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Empleados</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
    <h1 class="h4 fw-semibold mb-4">Nuevo empleado</h1>
    <p class="text-muted small">Se creará el empleado y su acceso al sistema (usuario con correo y contraseña). Podrá iniciar sesión con el rol asignado.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('employees.store') }}" method="post" class="needs-validation" novalidate>
                @csrf
                <h2 class="h6 fw-semibold text-primary mb-3">Datos de acceso al sistema</h2>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nombre del usuario <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" placeholder="Nombre para iniciar sesión">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="ejemplo@empresa.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" placeholder="Mínimo 8 caracteres">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label">Rol en el sistema <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">— Seleccione el rol —</option>
                            <option value="EMPLOYEE" {{ old('role') === 'EMPLOYEE' ? 'selected' : '' }}>Empleado</option>
                            <option value="AREA_MANAGER" {{ old('role') === 'AREA_MANAGER' ? 'selected' : '' }}>Gerente de Área</option>
                            <option value="HR_MANAGER" {{ old('role') === 'HR_MANAGER' ? 'selected' : '' }}>Gerente de Recursos Humanos</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Define qué puede hacer en el sistema (solicitar vacaciones, aprobar, gestionar empleados, etc.).</small>
                    </div>
                </div>

                <h2 class="h6 fw-semibold text-primary mb-3">Datos del empleado</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="area_id" class="form-label">Área <span class="text-danger">*</span></label>
                        <select id="area_id" name="area_id" class="form-select @error('area_id') is-invalid @enderror" required>
                            <option value="">— Seleccione el área —</option>
                            @foreach($areas as $a)
                                <option value="{{ $a['id'] }}" {{ (string)old('area_id') === (string)$a['id'] ? 'selected' : '' }}>{{ $a['name'] }}</option>
                            @endforeach
                        </select>
                        @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="employee_number" class="form-label">Número de empleado <span class="text-danger">*</span></label>
                        <input type="text" id="employee_number" name="employee_number" class="form-control @error('employee_number') is-invalid @enderror" value="{{ old('employee_number') }}" required maxlength="50" placeholder="Ej: EMP-001">
                        @error('employee_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required maxlength="100">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required maxlength="100">
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="hire_date" class="form-label">Fecha de ingreso <span class="text-danger">*</span></label>
                        <input type="date" id="hire_date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror" value="{{ old('hire_date') }}" required>
                        @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="vacation_days_annual" class="form-label">Días de vacaciones anual (opcional)</label>
                        <input type="number" id="vacation_days_annual" name="vacation_days_annual" class="form-control @error('vacation_days_annual') is-invalid @enderror" value="{{ old('vacation_days_annual') }}" min="0" max="365" placeholder="Se calcula por antigüedad">
                        @error('vacation_days_annual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Crear empleado y acceso</button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

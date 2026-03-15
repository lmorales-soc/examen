@extends('layouts.app')

@section('title', 'Editar área')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('areas.index') }}">Áreas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('areas.show', $area['id']) }}">{{ $area['name'] ?? 'Área' }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
    <h1 class="h4 fw-semibold mb-4">Editar área</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('areas.update', $area['id']) }}" method="post" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $area['name'] ?? '') }}" required maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug <span class="text-muted">(opcional)</span></label>
                        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $area['slug'] ?? '') }}" maxlength="100">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="manager_user_id" class="form-label">Gerente del área</label>
                        <select name="manager_user_id" id="manager_user_id" class="form-select @error('manager_user_id') is-invalid @enderror">
                            <option value="">— Ninguno —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (int)old('manager_user_id', $area['manager_user_id'] ?? 0) === (int)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('manager_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <hr>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        <a href="{{ route('areas.show', $area['id']) }}" class="btn btn-outline-secondary">Ver área</a>
                        <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

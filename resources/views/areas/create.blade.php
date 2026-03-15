@extends('layouts.app')

@section('title', 'Nueva área')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('areas.index') }}">Áreas</a></li>
            <li class="breadcrumb-item active">Nueva</li>
        </ol>
    </nav>
    <h1 class="h4 fw-semibold mb-4">Nueva área / departamento</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('areas.store') }}" method="post" class="needs-validation" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="100" placeholder="Ej: Desarrollo">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug <span class="text-muted">(opcional)</span></label>
                        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" maxlength="100" placeholder="Ej: desarrollo (se genera del nombre si se deja vacío)">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <hr>
                        <button type="submit" class="btn btn-primary">Crear área</button>
                        <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

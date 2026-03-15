@extends('layouts.app')

@section('title', $area['name'] ?? 'Área')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('areas.index') }}">Áreas</a></li>
            <li class="breadcrumb-item active">{{ $area['name'] ?? 'Área' }}</li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h4 mb-0 fw-semibold">{{ $area['name'] ?? '—' }}</h1>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('areas.edit', $area['id']) }}" class="btn btn-primary">Editar</a>
            <form action="{{ route('areas.destroy', $area['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta área?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Eliminar</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3 text-muted">ID</dt>
                <dd class="col-sm-9">{{ $area['id'] ?? '—' }}</dd>

                <dt class="col-sm-3 text-muted">Nombre</dt>
                <dd class="col-sm-9">{{ $area['name'] ?? '—' }}</dd>

                <dt class="col-sm-3 text-muted">Slug</dt>
                <dd class="col-sm-9"><code>{{ $area['slug'] ?? '—' }}</code></dd>

                <dt class="col-sm-3 text-muted">Gerente actual</dt>
                <dd class="col-sm-9">{{ $area['manager_name'] ?? 'Sin asignar' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-light">
            <h2 class="h6 mb-0 fw-semibold">Asignar o cambiar gerente del área</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('areas.update', $area['id']) }}" method="post" class="row g-2 align-items-end">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $area['name'] }}">
                <input type="hidden" name="slug" value="{{ $area['slug'] }}">
                <div class="col-auto flex-grow-1">
                    <label for="manager_user_id" class="form-label small">Usuario como gerente</label>
                    <select name="manager_user_id" id="manager_user_id" class="form-select form-select-sm">
                        <option value="">— Ninguno (desasignar) —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (int)($area['manager_user_id'] ?? 0) === (int)$u->id ? 'selected' : '' }}>{{ $u->name }} (ID {{ $u->id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Guardar gerente</button>
                </div>
            </form>
        </div>
    </div>

    <p class="mt-3">
        <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Volver al listado</a>
    </p>
@endsection

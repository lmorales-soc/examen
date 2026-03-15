@extends('layouts.app')

@section('title', 'Áreas / Departamentos')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h4 mb-0 fw-semibold">Áreas / Departamentos</h1>
        <a href="{{ route('areas.create') }}" class="btn btn-primary">Nueva área</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th>Gerente</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areas as $a)
                            <tr>
                                <td class="ps-3">{{ $a['id'] ?? '—' }}</td>
                                <td class="fw-medium">{{ $a['name'] ?? '—' }}</td>
                                <td><code class="small">{{ $a['slug'] ?? '—' }}</code></td>
                                <td>{{ $managerNames[$a['manager_user_id'] ?? 0] ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('areas.show', $a['id']) }}" class="btn btn-outline-primary">Ver</a>
                                        <a href="{{ route('areas.edit', $a['id']) }}" class="btn btn-outline-secondary">Editar</a>
                                        <form action="{{ route('areas.destroy', $a['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta área?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No hay áreas registradas. <a href="{{ route('areas.create') }}">Crear la primera</a>.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

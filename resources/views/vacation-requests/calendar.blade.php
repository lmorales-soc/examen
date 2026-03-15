@extends('layouts.app')

@section('title', 'Calendario de vacaciones')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('vacation-requests.index') }}">Solicitudes</a></li>
            <li class="breadcrumb-item active">Calendario</li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="page-title mb-0">Calendario de vacaciones</h1>
        <a href="{{ route('vacation-requests.index') }}" class="btn btn-outline-secondary">Volver a solicitudes</a>
    </div>
    <p class="text-muted mb-4">Vacaciones aprobadas: nombre del empleado, área y fechas.</p>

    @if(!empty($legend))
        <div class="card mb-3">
            <div class="card-body py-2">
                <p class="small text-muted mb-2 mb-md-0 me-2 d-inline">Personas con vacaciones @if(isset($year))({{ $year }})@endif:</p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @foreach($legend as $item)
                        <span class="badge border-0" style="background-color: {{ $item['color'] }}; color: #fff;">{{ $item['name'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body p-0">
            <div id="vacation-calendar"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 text-muted">Leyenda</h6>
        </div>
        <div class="card-body small text-muted">
            Cada evento muestra <strong>Nombre del empleado · Área</strong>. Al hacer clic se ven los detalles (días solicitados).
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('vacation-calendar');
            const eventsUrl = '{{ route("vacation-requests.calendar.events") }}';

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    list: 'Lista'
                },
                events: function(info, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr
                    });
                    fetch(eventsUrl + '?' + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                        .then(function(response) { return response.json(); })
                        .then(function(data) { successCallback(data); })
                        .catch(function() { failureCallback(); });
                },
                eventClick: function(info) {
                    const p = info.event.extendedProps;
                    const msg = (p.employeeName || '') + '\nÁrea: ' + (p.area || '—') + '\nDías: ' + (p.daysRequested || 0);
                    info.jsEvent.preventDefault();
                    alert(msg);
                },
                eventDidMount: function(info) {
                    info.el.title = info.event.extendedProps.employeeName + ' · ' + info.event.extendedProps.area + ' (' + info.event.extendedProps.daysRequested + ' días)';
                }
            });

            calendar.render();
        });
    </script>
@endpush

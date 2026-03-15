<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ejemplo de roles - {{ config('app.name') }}</title>
</head>
<body>
    <h1>Ejemplo de control por roles (Spatie)</h1>
    <p>{{ $message ?? 'Sin mensaje.' }}</p>
    <p><a href="{{ url('/') }}">Volver</a></p>
</body>
</html>

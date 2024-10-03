<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Time keeper') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Scripts -->
    <script src="{{ asset('js/app-backend.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app-backend.css') }}" rel="stylesheet">

    {{-- @vite(['resources/sass/app-backend.scss', 'resources/js/app-backend.js']) --}}
</head>

<body>
    <v-app id="app" class="h-100">
        <app-container></app-container>
    </v-app>
</body>

</html>

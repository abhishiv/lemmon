<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('/dist/css/app.css', '../') }}"/>
    <link rel="stylesheet" href="{{ mix('/scss/app.css', '../') }}">

    <!-- Scripts -->
    <script src="{{ asset('dist/js/app.js') }}" defer></script>
</head>
<body class="account">
<div class="container layout">
    @yield('content')
</div>
@include('layouts.parts.footer')
</body>
</html>

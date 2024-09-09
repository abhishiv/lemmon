<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="{{ url('') }}"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lemmon') }}</title>

    {{-- ############### JQUERY ###############--}}
    <script src="{{ mix('/dist/js/libraries/jquery.min.js', '../') }}"></script>
    <script>$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});</script>

    {{-- ############### jQuery UI ################ --}}
    <script src="{{ mix('/dist/js/libraries/jquery-ui.min.js', '../') }}"></script>
    <link href="{{ mix('/dist/css/libraries/jquery-ui.min.css', '../') }}" rel="stylesheet"/>

    {{-- ############### DataTable  ###############--}}
    <link rel="stylesheet" type="text/css"
          href="{{ mix('/dist/css/libraries/datatables.min.css', '../') }}"/>
    <script type="text/javascript" src="{{ mix('/dist/js/libraries/datatables.min.js', '../') }}"></script>

    <!-- Bootstrap JavaScript -->
    <script src="{{ mix('/dist/js/libraries/bootstrap.min.js', '../') }}"></script>

    {{-- ############### SORTABLE  ###############--}}
    <script src="{{ mix('/dist/js/libraries/jquery-sortable.min.js', '../') }}"></script>

    {{-- ############### SELECT ############### --}}
    <link href="{{ mix('/dist/css/libraries/select2.min.css', '../') }}" rel="stylesheet"/>
    <script src="{{ mix('/dist/js/libraries/select2.min.js', '../') }}"></script>

    <script src="{{ mix('/dist/js/libraries/masonry.min.js', '../') }}"></script>

    {{-- ############### DRIPZONE ############### --}}
    <script src="{{ mix('/dist/js/libraries/dropzone.min.js', '../') }}"></script>
    <link rel="stylesheet" href="{{ mix('/dist/css/libraries/dropzone.min.css', '../') }}" type="text/css"/>

    <script src="{{ mix('/dist/js/app.js', '../') }}" defer></script>
    @stack('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/dashboard-header.js', '../') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/general.js', '../')}}" defer></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ mix('/dist/css/app.css', '../') }}"/>
    <link rel="stylesheet" href="{{ mix('/scss/app.css', '../') }}">
    @stack('styles')
</head>

<body class="@yield('body_class') {{ App::environment(['production']) ? '' : 'development-environment' }}">
@include('layouts.parts.header')
<div class="container">
    <main class="table-container">
        @yield('content')
    </main>
</div>
@include('layouts.parts.footer')
</body>
</html>

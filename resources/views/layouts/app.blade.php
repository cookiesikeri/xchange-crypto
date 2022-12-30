<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{$site->site_name}}">
    <link rel="icon" href="">
    <link rel="icon" href="{{URL::to($site->logo)}}">

    <title>{{$site->site_name}} | @yield('title')</title>
    @include('layouts.css')
    @yield('css')

  </head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    @include('layouts.top-nav')
    @include('layouts.sidebar')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->


        <!-- Main content -->
        @yield('content')
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
      @include('layouts.footer')

    </div>


<!-- ./wrapper -->
@include('layouts.js')
<script>
    @if(Session::has('success'))
    new Noty({
        type: 'success',
        layout: 'topRight',
        text: '{{Session::get('success')}}'
    }).show();
    @endif

    @if(Session::has('fail'))
    new Noty({
        type: 'error',
        layout: 'topRight',
        text: '{{Session::get('fail')}}'
    }).show();
    @endif

    @if(Session::has('error'))
    new Noty({
        type: 'error',
        layout: 'topRight',
        text: '{{Session::get('error')}}'
    }).show();
    @endif

</script>
@yield('javascripts')


</body>
</html>

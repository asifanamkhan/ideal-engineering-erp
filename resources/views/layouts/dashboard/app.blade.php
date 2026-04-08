<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>IDEAl ENGINEERING</title>


    <!-- Custom fonts for this template-->
    <link href="{{ asset('public/dashboard/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet"
        type="text/css">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('public/dashboard/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/dashboard/css/style.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    @yield('css')
</head>

<body id="page-top">
    <div id="wrapper">
        @include('layouts.dashboard.sidebar')
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('layouts.dashboard.navbar')
                 @yield('content')

            </div>
            @include('layouts.dashboard.footer')
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    @include('layouts.dashboard.logout-modal')

    <script src="{{ asset('public/dashboard/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('public/dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/dashboard/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('public/dashboard/js/sb-admin-2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    {{-- <!-- Page level plugins -->
    <script src="{{ asset('public/dashboard/vendor/chart.js/Chart.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script src="{{ asset('public/dashboard/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('public/dashboard/js/demo/chart-pie-demo.js') }}"></script> --}}

    @yield('js')
</body>

</html>

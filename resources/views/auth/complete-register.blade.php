<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>RAPID - Candidate Registration</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('public/dashboard/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('public/dashboard/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }
        .section-title {
            color: #4e73df;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .registration-card {
            max-width: 1200px;
            width: 100%;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-register {
            padding: 0.75rem 3rem;
            font-size: 1.1rem;
        }
        .alert-success {
            border-left: 4px solid #1cc88a;
        }
        .success-icon {
            color: #1cc88a;
            font-size: 1.5rem;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="container-fluid">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-12">
                <div class="card o-hidden border-0 shadow-lg my-5 registration-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class=""><img style="width:20%" src="https://rapiderp.excellency-catering-restaurant-sweets.com/img/company/company_logo.png" alt=""></h1>
                                    </div>

                                    <!-- Success Message Section -->
                                    @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle success-icon mr-3"></i>
                                            <div>
                                                <h5 class="alert-heading mb-1">Registration Successful!</h5>
                                                <p class="mb-0">{{ session('success') }}</p>
                                            </div>
                                        </div>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    @if(session('warning'))
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle mr-3"></i>
                                            <div>
                                                <h5 class="alert-heading mb-1">Registration Completed</h5>
                                                <p class="mb-0">{{ session('warning') }}</p>
                                            </div>
                                        </div>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="{{ route('login') }}">
                                            <i class="fas fa-sign-in-alt mr-1"></i>Login!
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('public/dashboard/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('public/dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('public/dashboard/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('public/dashboard/js/sb-admin-2.min.js') }}"></script>

    <!-- Auto-dismiss alerts after 10 seconds -->
    <script>
        $(document).ready(function() {
            // Auto dismiss alerts after 10 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 10000);

            // Manual dismiss on close button click
            $('.alert .close').on('click', function() {
                $(this).closest('.alert').alert('close');
            });
        });
    </script>

</body>
</html>

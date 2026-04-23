<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IDEAL ENGINEERINGS WORKS</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Our Custom CSS -->
    <style>
        body {
            background-image: url("{{ asset('public/dashboard/img/login-bg.jpeg') }}");
            background-size: cover;
        }

        .area {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #333;
            background: #00000075
        }

        .container {
            width: 100%;
            max-width: 500px;
        }

        .card {
            /* background: #0000004f; */
            background: #13027d61;
            color: white;
            border-radius: 25px;
        }

        .form-control {
            background: #00000000 !important;
            color: white !important;
            border: none;
            border-bottom: 1px solid white;
            border-radius: inherit;
        }

        .btn-primary {
            box-shadow: none !important;
        }
    </style>
</head>

<body style="">
    <div class="area">
        <div class="container">
            <div class="container-fluid">
                <div class="">
                    <div class="card">
                        <div class="p-5">
                            @php
                            $name = DB::table('generel_settings')->first()
                            @endphp
                            <h4 style="text-align: center; font-size:2.2rem; margin-bottom:30px; color: white">
                                @if (@$name->conpany_name)
                                {{ $name->conpany_name }}
                                @else
                                IDEAL ENGINEERINGS WORKS
                                @endif
                            </h4>
                            <form method="POST" action="{{ route('login') }}" autocomplete="off">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="">Email </label>
                                    <input name='email' type='email'
                                        class="form-control @error('email') is-invalid @enderror">
                                    @error('email')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <br>
                                <div class="form-group mb-4">
                                    <label for="">Password </label>
                                    <input name='password' type='password' autocomplete="new-password"
                                        class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="text-center">
                                    <button style="padding: 0.4rem 1.2rem; font-size:20px" class="btn btn-primary"
                                        type="submit">Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

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

                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if(session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    <form class="user" method="POST" action="{{ route('candidate.store') }}" enctype="multipart/form-data">
                                        @csrf

                                        <!-- Personal Information Section -->
                                        <div class="form-section">
                                            <h5 class="section-title">Personal Information</h5>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="name">Full Name *</label>
                                                    <input name="name" type="text" class="form-control form-control-user"
                                                           id="name" value="{{ old('name') }}" required
                                                           placeholder="Enter Full Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="email">Email Address *</label>
                                                    <input name="email" type="email" class="form-control form-control-user"
                                                           id="email" value="{{ old('email') }}" required
                                                           placeholder="Enter Email Address">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="phone">Phone Number</label>
                                                    <input name="phone" type="text" class="form-control form-control-user"
                                                           id="phone" value="{{ old('phone') }}"
                                                           placeholder="Enter Phone Number">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="date_of_birth">Date of Birth</label>
                                                    <input name="date_of_birth" type="date" class="form-control form-control-user"
                                                           id="date_of_birth" value="{{ old('date_of_birth') }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="gender">Gender</label>
                                                    <select name="gender" class="form-control form-control-user" id="gender">
                                                        <option value="">Select Gender</option>
                                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="national_id">NID / Birth Certificate / Passport</label>
                                                    <input name="national_id" type="text" class="form-control form-control-user"
                                                           id="national_id" value="{{ old('national_id') }}"
                                                           placeholder="Enter ID Number">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Address Information Section -->
                                        <div class="form-section">
                                            <h5 class="section-title">Address Information</h5>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="address">Address</label>
                                                    <input name="address" type="text" class="form-control form-control-user"
                                                           id="address" value="{{ old('address') }}" placeholder="Enter Address">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="city">City</label>
                                                    <input name="city" type="text" class="form-control form-control-user"
                                                           id="city" value="{{ old('city') }}" placeholder="Enter City">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="state">State</label>
                                                    <input name="state" type="text" class="form-control form-control-user"
                                                           id="state" value="{{ old('state') }}" placeholder="Enter State">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="country">Country</label>
                                                    <input name="country" type="text" class="form-control form-control-user"
                                                           id="country" value="{{ old('country') }}" placeholder="Enter Country">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="postal_code">Postal Code</label>
                                                    <input name="postal_code" type="text" class="form-control form-control-user"
                                                           id="postal_code" value="{{ old('postal_code') }}" placeholder="Enter Postal Code">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Professional Information Section -->
                                        <div class="form-section">
                                            <h5 class="section-title">Professional Information</h5>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="current_company">Current Company</label>
                                                    <input name="current_company" type="text" class="form-control form-control-user"
                                                           id="current_company" value="{{ old('current_company') }}"
                                                           placeholder="Enter Current Company">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="current_position">Current Position</label>
                                                    <input name="current_position" type="text" class="form-control form-control-user"
                                                           id="current_position" value="{{ old('current_position') }}"
                                                           placeholder="Enter Current Position">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="total_experience">Total Experience (Years)</label>
                                                    <input name="total_experience" type="number" step="0.1" class="form-control form-control-user"
                                                           id="total_experience" value="{{ old('total_experience') }}"
                                                           placeholder="e.g., 3.5">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="current_salary">Current Salary</label>
                                                    <input name="current_salary" type="text" class="form-control form-control-user"
                                                           id="current_salary" value="{{ old('current_salary') }}"
                                                           placeholder="Enter Current Salary">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="expected_salary">Expected Salary</label>
                                                    <input name="expected_salary" type="text" class="form-control form-control-user"
                                                           id="expected_salary" value="{{ old('expected_salary') }}"
                                                           placeholder="Enter Expected Salary">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Online Profiles Section -->
                                        <div class="form-section">
                                            <h5 class="section-title">Online Profiles</h5>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="linkedin_profile">LinkedIn Profile</label>
                                                    <input name="linkedin_profile" type="url" class="form-control form-control-user"
                                                           id="linkedin_profile" value="{{ old('linkedin_profile') }}"
                                                           placeholder="https://linkedin.com/in/username">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="github_profile">GitHub Profile</label>
                                                    <input name="github_profile" type="url" class="form-control form-control-user"
                                                           id="github_profile" value="{{ old('github_profile') }}"
                                                           placeholder="https://github.com/username">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="portfolio_website">Portfolio Website</label>
                                                    <input name="portfolio_website" type="url" class="form-control form-control-user"
                                                           id="portfolio_website" value="{{ old('portfolio_website') }}"
                                                           placeholder="https://yourportfolio.com">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Application Details Section -->
                                        <div class="form-section">
                                            <h5 class="section-title">Application Details</h5>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="job_id">Job Position Applied For</label>
                                                    <input name="job_id" type="text" class="form-control form-control-user"
                                                           id="job_id" value="{{ old('job_id') }}"
                                                           placeholder="Enter Job ID or Position">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="resume">Upload Resume (PDF/DOC)</label>
                                                    <input name="resume" type="file" class="form-control form-control-user"
                                                           id="resume" accept=".pdf,.doc,.docx">
                                                    <small class="form-text text-muted">Max file size: 5MB</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="text-align: center; margin-top: 2rem;">
                                            <button type="submit" class="btn btn-primary btn-user btn-register">
                                                <i class="fas fa-user-plus mr-2"></i>Register Account
                                            </button>
                                        </div>
                                    </form>

                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="{{ route('login') }}">
                                            <i class="fas fa-sign-in-alt mr-1"></i>Already have an account? Login!
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

</body>
</html>

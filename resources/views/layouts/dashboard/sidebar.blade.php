<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <div class="sidebar-brand-icon">
            Ideal Engineering
            {{-- <img style="width:60%" src="{{ asset('public/dashboard/img/logo.png') }}" alt=""> --}}
        </div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item
        @if (Route::is('dashboard'))
            active
        @endif">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>


    <li class="nav-item" id="candidate-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCandidate" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Candidates</span>
        </a>
        <div id="collapseCandidate" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="candidate-index-sidebar" href="{{ route('candidates.index') }}">Candidate List</a>
                <a class="collapse-item" id="candidate-create-sidebar" href="{{ route('candidates.create') }}">New Candidates</a>
            </div>
        </div>
    </li>

    {{-- <li class="nav-item" id="candidate-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExam" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Exam</span>
        </a>
        <div id="collapseExam" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="candidate-index-sidebar" href="{{ route('exams.index') }}">Exam List</a>
                <a class="collapse-item" id="candidate-create-sidebar" href="{{ route('exam.start') }}">New Exam</a>
            </div>
        </div>
    </li> --}}

    <li class="nav-item" id="job-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseJob" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Job Posts</span>
        </a>
        <div id="collapseJob" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="job-index-sidebar" href="{{ route('job-posts.index') }}">Job List</a>
                <a class="collapse-item" id="job-create-sidebar" href="{{ route('job-posts.create') }}">New Job Post</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="settings-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettings" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Settings</span>
        </a>
        <div id="collapseSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="department-index-sidebar" href="{{ route('departments.index') }}">Departments</a>
                {{-- <a class="collapse-item" id="job-create-sidebar" href="{{ route('job-posts.create') }}">New Job Post</a> --}}
            </div>
        </div>
    </li>

</ul>

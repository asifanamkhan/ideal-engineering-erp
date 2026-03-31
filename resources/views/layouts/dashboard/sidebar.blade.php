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
        @if (Route::is('admin.dashboard'))
            active
        @endif">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <li class="nav-item" id="settings-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettings" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Settings</span>
        </a>
        <div id="collapseSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="gsettings-index-sidebar" href="{{ route('admin.general-settings.index') }}">General settings</a>
                <a class="collapse-item" id="branch-index-sidebar" href="{{ route('admin.branches.index') }}">Branch</a>
                <a class="collapse-item" id="users-index-sidebar" href="{{ route('admin.users.index') }}">Users</a>
                <a class="collapse-item" id="roles-index-sidebar" href="{{ route('admin.roles.index') }}">Roles</a>
                <a class="collapse-item" id="parts-index-sidebar" href="{{ route('admin.parts.index') }}">Parts</a>
                @can('view_tests')
                <a class="collapse-item" id="employee-create-sidebar" href="{{ route('admin.employees.create') }}">Service</a>
                @endcan
            </div>
        </div>
    </li>
    <li class="nav-item" id="hrm-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseHRM" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>HRM</span>
        </a>
        <div id="collapseHRM" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="designations-index-sidebar" href="{{ route('admin.designations.index') }}">Designation</a>
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('admin.employees.index') }}">Employee</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="{{ route('admin.employees.salary-info') }}">Salary Information</a>

            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Customers</span></a>
    </li>
    {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Jobs</span></a>
    </li> --}}
    {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Quotations</span></a>
    </li> --}}
    {{-- <li class="nav-item" id="employee-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseemployee" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>employees</span>
        </a>
        <div id="collapseemployee" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('employees.index') }}">employee List</a>
                <a class="collapse-item" id="employee-create-sidebar" href="{{ route('employees.create') }}">New employees</a>
            </div>
        </div>
    </li> --}}

    {{-- <li class="nav-item" id="employee-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExam" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Exam</span>
        </a>
        <div id="collapseExam" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('exams.index') }}">Exam List</a>
                <a class="collapse-item" id="employee-create-sidebar" href="{{ route('exam.start') }}">New Exam</a>
            </div>
        </div>
    </li> --}}

    {{-- <li class="nav-item" id="job-sidebar">
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
    </li> --}}

    {{-- <li class="nav-item" id="settings-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettings" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Settings</span>
        </a>
        <div id="collapseSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="department-index-sidebar" href="{{ route('departments.index') }}">Departments</a>
            </div>
        </div>
    </li> --}}

</ul>

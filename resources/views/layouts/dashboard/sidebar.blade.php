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
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <div id="collapseSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="gsettings-index-sidebar" href="{{ route('admin.general-settings.index') }}">General settings</a>
                <a class="collapse-item" id="branch-index-sidebar" href="{{ route('admin.branches.index') }}">Branch</a>
                <a class="collapse-item" id="units-index-sidebar" href="{{ route('admin.units.index') }}">Units</a>
                <a class="collapse-item" id="sizes-index-sidebar" href="{{ route('admin.sizes.index') }}">Sizes</a>
                <a class="collapse-item" id="parts-index-sidebar" href="{{ route('admin.parts.index') }}">Parts</a>
                @can('view_tests')
                <a class="collapse-item" id="services-index-sidebar" href="{{ route('admin.services.index') }}">Services</a>

                @endcan
            </div>
        </div>
    </li>
    <li class="nav-item" id="user-management-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUserManagement" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-user"></i>
            <span>User Management</span>
        </a>
        <div id="collapseUserManagement" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="users-index-sidebar" href="{{ route('admin.users.index') }}">Users</a>
                <a class="collapse-item" id="roles-index-sidebar" href="{{ route('admin.roles.index') }}">Roles</a>
            </div>
        </div>
    </li>
    <li class="nav-item" id="contacts-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseContacts" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-address-card"></i>
            <span>Contacts</span>
        </a>
        <div id="collapseContacts" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="customers-index-sidebar" href="{{ route('admin.customers.index') }}">Customers</a>
                <a class="collapse-item" id="suppliers-index-sidebar" href="{{ route('admin.suppliers.index') }}">Suppliers</a>
            </div>
        </div>
    </li>
    <li class="nav-item" id="jobs-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseJobs" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-address-card"></i>
            <span>Jobs</span>
        </a>
        <div id="collapseJobs" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="jobs-index-sidebar" href="{{ route('admin.job-books.index') }}">Job lists</a>
                <a class="collapse-item" id="quotations-index-sidebar" href="{{ route('admin.job-quotations.create') }}">Quotetions</a>
                <a class="collapse-item" id="invoices-index-sidebar" href="{{ route('admin.job-invoices.create') }}">Invoices</a>
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

    <li class="nav-item" id="hrm-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseHRM" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Expenses</span>
        </a>
        <div id="collapseHRM" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="designations-index-sidebar" href="{{ route('admin.designations.index') }}">Designation</a>
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('admin.employees.index') }}">Employee</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="{{ route('admin.employees.salary-info') }}">Salary Information</a>

            </div>
        </div>
    </li>
    <li class="nav-item" id="hrm-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseHRM" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Reports</span>
        </a>
        <div id="collapseHRM" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="designations-index-sidebar" href="{{ route('admin.designations.index') }}">Designation</a>
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('admin.employees.index') }}">Employee</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="{{ route('admin.employees.salary-info') }}">Salary Information</a>

            </div>
        </div>
    </li>
    <li class="nav-item" id="hrm-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseHRM" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Accounts</span>
        </a>
        <div id="collapseHRM" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="designations-index-sidebar" href="{{ route('admin.designations.index') }}">Designation</a>
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('admin.employees.index') }}">Employee</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="{{ route('admin.employees.salary-info') }}">Salary Information</a>

            </div>
        </div>
    </li>
</ul>

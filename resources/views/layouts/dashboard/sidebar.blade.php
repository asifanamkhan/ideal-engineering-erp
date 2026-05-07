<style>
    .collapse-header {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.collapse-inner .collapse-item {
    padding: 8px 20px 8px 35px;
    font-size: 13px;
}

.collapse-inner .collapse-item:hover {
    background-color: #f0f0f0;
}
</style>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
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
                <a class="collapse-item" id="parts-index-sidebar" href="{{ route('admin.parts.index') }}">Purse</a>
                <a class="collapse-item" id="job_descriptions-index-sidebar" href="{{ route('admin.job_descriptions.index') }}">Job Descriptions</a>
                @can('view_tests')
                <a class="collapse-item" id="services-index-sidebar" href="{{ route('admin.services.index') }}">Services</a>
                <a class="collapse-item" id="sms-index-sidebar" href="{{ route('admin.sms.settings') }}">SMS Settings</a>
                <a class="collapse-item" id="invoice-settings-index-sidebar" href="{{ route('admin.invoice-settings.index') }}">Invoice Settings</a>

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
            <i class="fas fa-asterisk"></i>
            <span>Jobs</span>
        </a>
        <div id="collapseJobs" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="jobs-index-sidebar" href="{{ route('admin.job-books.index') }}">Job lists</a>
                <a class="collapse-item" id="quotations-index-sidebar" href="{{ route('admin.job-quotations.index') }}">Quotetions</a>
                <a class="collapse-item" id="invoices-index-sidebar" href="{{ route('admin.job-invoices.index') }}">Invoices</a>
                <a class="collapse-item" id="parts-index-sidebar" href="{{ route('admin.job-parts.index') }}">Purse List</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="hrm-sidebar">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseHRM" aria-expanded="true"
            aria-controls="collapseHRM">
            <i class="fas fa-users"></i>
            <span>HRM</span>
        </a>
        <div id="collapseHRM" class="collapse" aria-labelledby="headingHRM" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="hrm-settings-sidebar" href="{{ route('admin.hrm.settings.index') }}">HRM Settings</a>
                <!-- Employee Management -->
                <a class="collapse-item" id="designations-index-sidebar" href="{{ route('admin.designations.index') }}">Designation</a>
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('admin.employees.index') }}">Employee</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="{{ route('admin.employees.salary-info') }}">Allowance & Deduction</a>

                <a class="collapse-item" id="weekend-settings-sidebar" href="{{ route('admin.hrm.weekend-settings.index') }}">Weekend Settings</a>
                <!-- Leave Module -->
                <div class="collapse-header text-muted small px-3 mt-2 mb-1">LEAVE</div>
                <a class="collapse-item" id="leave-types-sidebar" href="{{ route('admin.hrm.leave-types.index') }}">Leave Types</a>
                <a class="collapse-item" id="leave-apply-sidebar" href="{{ route('admin.hrm.leave-applications.index') }}">Leave Applications</a>
                <a class="collapse-item" id="leave-balance-sidebar" href="{{ route('admin.hrm.leave-applications.balance') }}">Leave Balance</a>


                <!-- Attendance Module -->
                <div class="collapse-header text-muted small px-3 mt-2 mb-1">ATTENDANCE</div>
                <a class="collapse-item" id="attendance-daily-sidebar" href="{{ route('admin.hrm.attendance.index') }}">Daily Attendance</a>
                <a class="collapse-item" id="attendance-details-report-sidebar" href="{{ route('admin.hrm.attendance.details-report') }}">Month Wise Attendance</a>
                <a class="collapse-item" id="attendance-report-sidebar" href="{{ route('admin.hrm.attendance.report') }}">Attendance Summary Report</a>

                <div class="collapse-header text-muted small px-3 mt-2 mb-1">OVERTIME</div>
                <a class="collapse-item" id="overtime-sidebar" href="{{ route('admin.hrm.overtime.index') }}">Overtime List</a>
                <a class="collapse-item" id="overtime-date-wise-sidebar" href="{{ route('admin.hrm.overtime.date-wise') }}">Date-wise Entry</a>
                <a class="collapse-item" id="overtime-employee-wise-sidebar" href="{{ route('admin.hrm.overtime.employee-wise') }}">Employee-wise Entry</a>

                <div class="collapse-header text-muted small px-3 mt-2 mb-1">TIMESHEET</div>
                <a class="collapse-item" id="timesheet-sidebar" href="{{ route('admin.hrm.timesheet.index') }}">Timesheet</a>
                <!-- Salary Module -->
                <div class="collapse-header text-muted small px-3 mt-2 mb-1">SALARY</div>
                <a class="collapse-item" id="salary-generate-sidebar" href="{{ route('admin.hrm.salary.create') }}">Generate & update Salary</a>
                <a class="collapse-item" id="salary-records-sidebar" href="{{ route('admin.hrm.salary.index') }}">Salary Records</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="expense-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExpense" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-money-bill-wave"></i>
            <span>Expenses</span>
        </a>
        <div id="collapseExpense" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="expense-category-index-sidebar" href="{{ route('admin.expense-categories.index') }}">Category</a>
                <a class="collapse-item" id="expenses-index-sidebar" href="{{ route('admin.expenses.index') }}">Expense</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="payments-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayments" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Payments</span>
        </a>
        <div id="collapsePayments" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="payments-index-sidebar" href="{{ route('admin.payments.index') }}">All payment</a>
                <a class="collapse-item" id="payments-customer-payment-sidebar" href="{{ route('admin.payments.customer-payment.create') }}">Customer payment</a>

            </div>
        </div>
    </li>
    <li class="nav-item" id="report-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Reports</span>
        </a>
        <div id="collapseReport" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" id="designations-index-sidebar" href="#">Jobs</a>
                <a class="collapse-item" id="employee-index-sidebar" href="#">Quotetions</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="#">Expense</a>

            </div>
        </div>
    </li>
    <li class="nav-item" id="hrm-sidebar"">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccounts" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-users"></i>
            <span>Accounts</span>
        </a>
        <div id="collapseAccounts" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                {{-- <a class="collapse-item" id="designations-index-sidebar" href="{{ route('admin.designations.index') }}">Designation</a>
                <a class="collapse-item" id="employee-index-sidebar" href="{{ route('admin.employees.index') }}">Employee</a>
                <a class="collapse-item" id="employee-salary-info-sidebar" href="{{ route('admin.employees.salary-info') }}">Salary Information</a> --}}
            </div>
        </div>
    </li>
</ul>

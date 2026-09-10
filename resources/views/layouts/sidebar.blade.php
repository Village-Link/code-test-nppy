<aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}" class="brand-link" id="appSidebarLabel">
            <span class="brand-mark"><i class="bi bi-bank"></i></span>
            <span>
                <strong>Loan Management</strong>
                <small>{{ auth()->user()->getRoleNames()->first() === 'loan_officer' ? 'Loan Officer' : ucfirst(auth()->user()->getRoleNames()->first()) }} Panel</small>
            </span>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section">Management</span>

        @role('admin')
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('admin.loans.index') }}" class="sidebar-link {{ request()->routeIs('admin.loans.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i><span>Loan Applications</span>
            </a>
        @endrole

        @role('loan_officer')
            <a href="{{ route('loan-officer.dashboard') }}" class="sidebar-link {{ request()->routeIs('loan-officer.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('loan-officer.loans.index') }}" class="sidebar-link {{ request()->routeIs('loan-officer.loans.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i><span>Assigned Loans</span>
            </a>
        @endrole

        @role('customer')
            <a href="{{ route('customer.dashboard') }}" class="sidebar-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i><span>Summary</span>
            </a>
            <a href="{{ route('customer.loans.index') }}" class="sidebar-link {{ request()->routeIs('customer.loans.index', 'customer.loans.show') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i><span>Application History</span>
            </a>
            <a href="{{ route('customer.repayments.index') }}" class="sidebar-link {{ request()->routeIs('customer.repayments.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i><span>Repayment History</span>
            </a>
            <a href="{{ route('customer.loans.create') }}" class="sidebar-link {{ request()->routeIs('customer.loans.create') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-plus"></i><span>New Application</span>
            </a>
        @endrole

        <span class="nav-section mt-4">System</span>
        <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i><span>Notifications</span>
            @if ($unreadCount > 0)
                <span class="sidebar-count">{{ $unreadCount }}</span>
            @endif
        </a>
    </nav>
</aside>

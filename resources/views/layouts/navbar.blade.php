<header class="app-topbar">
    <button class="sidebar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open navigation">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-date d-none d-sm-flex">
        <i class="bi bi-calendar3"></i>
        <span>{{ now()->format('l, d F Y') }}</span>
    </div>

    <div class="user-summary ms-auto">
        <a href="{{ route('notifications.index') }}" class="topbar-notifications" aria-label="Notifications" title="Notifications">
            <i class="bi bi-bell"></i>
            @if ($unreadCount > 0)
                <span>{{ $unreadCount }}</span>
            @endif
        </a>

        <div class="user-copy d-none d-sm-block">
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ auth()->user()->email }}</small>
        </div>

        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>

        <form action="{{ route('logout') }}" method="POST" class="topbar-logout-form">
            @csrf
            <button type="submit" class="topbar-logout" title="Logout" aria-label="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</header>

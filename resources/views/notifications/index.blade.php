@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">Review your latest loan activity and status updates.</p>
        </div>
        @if (auth()->user()->unreadLoanNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-primary">Mark All as Read</button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                        <span class="notification-icon"><i class="bi bi-bell"></i></span>
                        <span class="notification-content">
                            <strong>{{ $notification->message }}</strong>
                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                        </span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>
                </form>
            @empty
                <div class="empty-state"><i class="bi bi-bell fs-2 d-block mb-2"></i>No notifications found.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ $notifications->links() }}</div>
</div>
@endsection

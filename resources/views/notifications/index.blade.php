@extends('layouts.app')
@section('title','Notifications')
@section('heading','Notifications')
@section('subtitle','Updates about requests, approvals, and account activity.')
@section('content')
<div class="page-actions">
    <div></div>
    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button class="button secondary">Mark all as read</button>
    </form>
</div>

<div class="section-card">
    <div class="notification-list">
        @forelse($notifications as $n)
            <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="notification-form">
                @csrf
                <button type="submit" class="notification {{ !$n->read_at ? 'unread' : '' }}">
                    <span class="notification-icon">{{ !$n->read_at ? '●' : '✓' }}</span>
                    <span>
                        <strong>{{ $n->data['message'] ?? 'Notification' }}</strong>
                        <small>{{ $n->created_at->diffForHumans() }}</small>
                    </span>
                </button>
            </form>
        @empty
            <div class="empty">You're all caught up.</div>
        @endforelse
    </div>
    <div class="pagination">{{ $notifications->links() }}</div>
</div>
@endsection

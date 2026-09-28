<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title','LeaveFlow') · Leave Management</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"></head><body>
@auth
<div class="app-shell"><aside class="sidebar"><a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">LR</span><span><strong>LeaveFlow</strong><small>Leave Management</small></span></a><nav>
<a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Dashboard</a>
<a class="nav-link {{ request()->routeIs('leave-requests.*')?'active':'' }}" href="{{ route('leave-requests.index') }}">Leave Requests</a>
@if(auth()->user()->isManager())<a class="nav-link {{ request()->routeIs('approvals.*')?'active':'' }}" href="{{ route('approvals.index') }}">Approvals @if(auth()->user()->pendingApprovalsCount())<span class="nav-badge">{{ auth()->user()->pendingApprovalsCount() }}</span>@endif</a>@endif
<a class="nav-link {{ request()->routeIs('calendar.*')?'active':'' }}" href="{{ route('calendar.index') }}">Calendar</a>
<a class="nav-link {{ request()->routeIs('notifications.*')?'active':'' }}" href="{{ route('notifications.index') }}">Notifications @if(auth()->user()->unreadNotifications()->count())<span class="nav-badge">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a>
@if(auth()->user()->isAdmin())<div class="nav-section">Administration</div><a class="nav-link {{ request()->routeIs('users.*')?'active':'' }}" href="{{ route('users.index') }}">Users & Managers</a><a class="nav-link {{ request()->routeIs('leave-types.*')?'active':'' }}" href="{{ route('leave-types.index') }}">Leave Types</a>@endif
</nav><div class="sidebar-bottom"><div class="profile-mini"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role }}</small></div></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-btn">Sign out</button></form></div></aside>
<main class="main"><header class="topbar"><div><h1>@yield('heading','Dashboard')</h1><p>@yield('subtitle','Manage leave requests with clarity and confidence.')</p></div><div class="top-user"><span class="status-dot"></span> {{ auth()->user()->name }}</div></header>
@if(session('success'))<div class="alert success">✓ {{ session('success') }}</div>@endif @if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif @if($errors->any())<div class="alert error"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@yield('content')</main></div>
@else @yield('guest') @endauth
</body></html>

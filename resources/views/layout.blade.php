<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Leave Request System</title>
    <style>
        :root { --bg:#f4f6fa; --card:#fff; --ink:#1f2937; --muted:#6b7280; --line:#e5e7eb; --brand:#2563eb; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; background:var(--bg); color:var(--ink); }
        nav { background:#111827; color:#fff; display:flex; gap:1rem; align-items:center; padding:.75rem 1.5rem; flex-wrap:wrap; }
        nav a { color:#d1d5db; text-decoration:none; font-size:.95rem; }
        nav a:hover, nav a.active { color:#fff; }
        nav .brand { font-weight:700; color:#fff; margin-right:1rem; }
        nav .spacer { flex:1; }
        .badge { background:#ef4444; color:#fff; border-radius:999px; font-size:.7rem; padding:.1rem .45rem; margin-left:.25rem; }
        main { max-width:1000px; margin:1.5rem auto; padding:0 1rem; }
        h1 { margin:0 0 1rem; font-size:1.5rem; }
        h2 { font-size:1.1rem; margin:1.5rem 0 .75rem; }
        .card { background:var(--card); border:1px solid var(--line); border-radius:10px; padding:1rem 1.25rem; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; }
        .stat b { font-size:1.8rem; }
        .muted { color:var(--muted); font-size:.85rem; }
        table { width:100%; border-collapse:collapse; background:var(--card); border:1px solid var(--line); border-radius:10px; overflow:hidden; }
        th, td { text-align:left; padding:.6rem .8rem; border-bottom:1px solid var(--line); font-size:.9rem; vertical-align:top; }
        th { background:#f9fafb; color:var(--muted); font-weight:600; }
        label { display:block; font-weight:600; margin:.9rem 0 .3rem; }
        input, select, textarea { width:100%; padding:.55rem .7rem; border:1px solid #d1d5db; border-radius:8px; font:inherit; }
        button, .btn { display:inline-block; background:var(--brand); color:#fff; border:0; border-radius:8px; padding:.5rem .95rem; font:inherit; cursor:pointer; text-decoration:none; }
        .btn-danger { background:#dc2626; } .btn-ok { background:#16a34a; } .btn-light { background:#e5e7eb; color:#111; }
        .btn-sm { padding:.25rem .6rem; font-size:.8rem; }
        .alert { padding:.7rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.ok { background:#dcfce7; color:#166534; } .alert.err { background:#fee2e2; color:#991b1b; }
        .alert ul { margin:0; padding-left:1.1rem; }
        .pill { padding:.15rem .55rem; border-radius:999px; font-size:.75rem; font-weight:600; }
        .pending { background:#fef3c7; color:#92400e; } .approved { background:#dcfce7; color:#166534; }
        .rejected { background:#fee2e2; color:#991b1b; } .cancelled, .skipped, .waiting { background:#e5e7eb; color:#374151; }
        .dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:.3rem; }
        .inline { display:inline-flex; gap:.4rem; align-items:center; }
        .inline input { width:150px; padding:.3rem .5rem; }
        .cal { table-layout:fixed; } .cal td { height:105px; padding:.3rem; }
        .cal .off { background:#f9fafb; color:#9ca3af; } .cal .today { outline:2px solid var(--brand); outline-offset:-2px; }
        .chip { display:block; color:#fff; border-radius:4px; font-size:.7rem; padding:.1rem .3rem; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .chip.pending { opacity:.55; }
    </style>
</head>
<body>
@auth
    <nav>
        <span class="brand">Leave System</span>
        <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>My Leave</a>
        <a href="{{ route('leave.create') }}" @class(['active' => request()->routeIs('leave.create')])>New Request</a>
        <a href="{{ route('approvals.index') }}" @class(['active' => request()->routeIs('approvals.*')])>Approvals
            @if(($n = auth()->user()->pendingApprovalsCount()) > 0)<span class="badge">{{ $n }}</span>@endif
        </a>
        <a href="{{ route('calendar.index') }}" @class(['active' => request()->routeIs('calendar.*')])>Calendar</a>
        <a href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])>Notifications
            @if(($n = auth()->user()->unreadNotifications()->count()) > 0)<span class="badge">{{ $n }}</span>@endif
        </a>
        <span class="spacer"></span>
        <span class="muted">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-light btn-sm">Logout</button></form>
    </nav>
@endauth
<main>
    @if(session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="alert err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
</body>
</html>

@extends('layout')
@section('title', 'My Leave')
@section('content')
<h1>My Leave <span class="muted">({{ $year }} balances)</span></h1>

<div class="grid">
    @foreach($balances as $b)
        <div class="card stat">
            <div><span class="dot" style="background:{{ $b['type']->color }}"></span>{{ $b['type']->name }}</div>
            @if($b['type']->tracks_balance)
                <b>{{ $b['available'] }}</b> <span class="muted">of {{ $b['allocated'] }} days available</span>
                <div class="muted">Used {{ $b['used'] }} · Pending {{ $b['pending'] }}</div>
            @else
                <b>∞</b> <span class="muted">not tracked</span>
            @endif
        </div>
    @endforeach
</div>

<h2>My requests <a class="btn btn-sm" href="{{ route('leave.create') }}" style="float:right">+ New request</a></h2>
<table>
    <tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Approval chain</th><th></th></tr>
    @forelse($requests as $r)
        <tr>
            <td><span class="dot" style="background:{{ $r->type->color }}"></span>{{ $r->type->name }}</td>
            <td>{{ $r->start_date->format('M j, Y') }} – {{ $r->end_date->format('M j, Y') }}
                @if($r->reason)<div class="muted">{{ $r->reason }}</div>@endif</td>
            <td>{{ $r->days }}</td>
            <td><span class="pill {{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
            <td>
                @foreach($r->approvals as $a)
                    <div>{{ $a->step }}. {{ $a->approver->name }} <span class="pill {{ $a->status }}">{{ $a->status }}</span>
                        @if($a->comment)<span class="muted">"{{ $a->comment }}"</span>@endif</div>
                @endforeach
            </td>
            <td>
                @if($r->isCancellable())
                    <form method="POST" action="{{ route('leave.cancel', $r) }}" onsubmit="return confirm('Cancel this request?')">
                        @csrf<button class="btn-danger btn-sm">Cancel</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="muted">No leave requests yet.</td></tr>
    @endforelse
</table>
@endsection

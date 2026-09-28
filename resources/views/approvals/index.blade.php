@extends('layouts.app')

@section('title','Approvals')
@section('heading','Leave Approvals')
@section('subtitle','Review leave requests submitted by Staff assigned to you.')

@section('content')
<div class="page-actions">
    <div class="filters">
        <form method="GET">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['pending','approved','rejected','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="button secondary" type="submit">Filter</button>
            @if(request()->filled('status'))
                <a class="button secondary" href="{{ route('approvals.index') }}">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="section-card">
    <div class="section-header">
        <div>
            <h2>Assigned Staff Requests</h2>
            <p>Only requests assigned to your Manager account are shown here.</p>
        </div>
        <span class="status pending">{{ $requests->total() }} request(s)</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Leave Type</th>
                    <th>Dates</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Decision</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>
                            <strong>{{ $request->user->name }}</strong>
                            <small class="muted">{{ $request->user->email }}</small>
                            <a class="detail-link" href="{{ route('leave-requests.show',$request) }}">View details →</a>
                        </td>
                        <td>{{ $request->type->name }}</td>
                        <td>
                            {{ $request->start_date->format('M d, Y') }}<br>
                            <small class="muted">to {{ $request->end_date->format('M d, Y') }}</small>
                        </td>
                        <td>{{ $request->days }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($request->reason, 90) }}</td>
                        <td><span class="status {{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                        <td>
                            @if($request->status === 'pending')
                                <div class="decision-actions">
                                    <form method="POST" action="{{ route('approvals.approve',$request) }}" onsubmit="return confirm('Approve this leave request?')">
                                        @csrf
                                        <input type="text" name="remarks" maxlength="1000" placeholder="Optional approval remark">
                                        <button class="button success-btn" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.reject',$request) }}" onsubmit="return confirm('Reject this leave request? A rejection reason is required.')">
                                        @csrf
                                        <input type="text" name="remarks" maxlength="1000" required placeholder="Rejection reason">
                                        <button class="button danger" type="submit">Reject</button>
                                    </form>
                                </div>
                            @else
                                <span class="muted">Decision recorded</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty">
                            <strong>No leave requests found.</strong>
                            <br>
                            <small>Your assigned Staff requests will appear here when submitted.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $requests->links() }}</div>
</div>
@endsection

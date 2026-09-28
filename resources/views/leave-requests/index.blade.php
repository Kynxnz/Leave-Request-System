@extends('layouts.app')
@section('title','Leave Requests')
@section('heading','Leave Requests')
@section('subtitle',auth()->user()->isStaff()?'Your submitted requests and their status.':'Requests available to your role.')
@section('content')
<div class="page-actions"><div class="filters"><form method="GET"><input name="search" value="{{ request('search') }}" placeholder="Search Staff..."><select name="status"><option value="">All statuses</option>@foreach(['pending','approved','rejected','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="button secondary">Filter</button></form></div>@if(auth()->user()->isStaff())<a class="button primary" href="{{ route('leave-requests.create') }}">+ Request Leave</a>@endif</div>
<div class="section-card"><div class="table-wrap"><table><thead><tr><th>Staff</th><th>Leave Type</th><th>Dates</th><th>Days</th><th>Manager</th><th>Status</th><th></th></tr></thead><tbody>@forelse($requests as $r)<tr><td>{{ $r->user->name }}</td><td>{{ $r->type->name }}</td><td>{{ $r->start_date->format('M d, Y') }} – {{ $r->end_date->format('M d, Y') }}</td><td>{{ $r->days }}</td><td>{{ $r->manager->name }}</td><td><span class="status {{ $r->status }}">{{ ucfirst($r->status) }}</span></td><td><a href="{{ route('leave-requests.show',$r) }}">View</a></td></tr>@empty<tr><td colspan="7" class="empty">No requests match your filters.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $requests->links() }}</div></div>
@endsection

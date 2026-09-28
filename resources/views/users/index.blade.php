@extends('layouts.app')

@section('title','Users & Managers')
@section('heading','Users & Managers')
@section('subtitle','Manage roles, account status, and Staff-to-Manager assignments.')

@section('content')
@if($unassignedStaff > 0)
    <div class="alert error">
        <strong>{{ $unassignedStaff }} active Staff account{{ $unassignedStaff === 1 ? '' : 's' }} need{{ $unassignedStaff === 1 ? 's' : '' }} a Manager assignment.</strong>
        <div>Staff without an active Manager cannot submit leave requests. Assign a Manager below before they file leave.</div>
    </div>
@endif

<div class="section-card">
    <div class="page-actions">
        <form class="filters" method="GET">
            <input name="search" value="{{ request('search') }}" placeholder="Search name or email">
            <select name="role">
                <option value="">All roles</option>
                @foreach(['Administrator','Manager','Staff'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <button class="button secondary" type="submit">Filter</button>
            @if(request()->hasAny(['search','role']))
                <a class="button secondary" href="{{ route('users.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Assigned Manager</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <small class="muted">{{ $user->email }}</small>
                        </td>
                        <td><span class="role-badge {{ strtolower($user->role) }}">{{ $user->role }}</span></td>
                        <td>
                            @if($user->isStaff())
                                @if($user->manager)
                                    <strong>{{ $user->manager->name }}</strong>
                                    @if($user->manager->status !== 'active')
                                        <small class="muted">Manager is inactive</small>
                                    @endif
                                @else
                                    <span class="status rejected">Not assigned</span>
                                @endif
                            @else
                                <span class="muted">Not applicable</span>
                            @endif
                        </td>
                        <td><span class="status {{ $user->status === 'active' ? 'approved' : 'cancelled' }}">{{ ucfirst($user->status) }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('users.update',$user) }}" class="user-edit">
                                @csrf
                                @method('PUT')

                                <select name="role" aria-label="Role">
                                    @foreach(['Administrator','Manager','Staff'] as $role)
                                        <option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>

                                <select name="manager_id" aria-label="Assigned Manager">
                                    <option value="">No manager</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" @selected($user->manager_id === $manager->id)>{{ $manager->name }}</option>
                                    @endforeach
                                </select>

                                <select name="status" aria-label="Account status">
                                    <option value="active" @selected($user->status === 'active')>Active</option>
                                    <option value="inactive" @selected($user->status === 'inactive')>Inactive</option>
                                </select>

                                <input type="password" name="password" placeholder="New password (optional)" autocomplete="new-password">

                                <button class="button primary" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No users match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $users->links() }}</div>
</div>
@endsection

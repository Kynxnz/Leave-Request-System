@extends('layouts.app')

@section('title','Request Leave')
@section('heading','Request Leave')
@section('subtitle','Submit your leave request to your assigned Manager.')

@section('content')
@if(!auth()->user()->manager || !auth()->user()->manager->isManager() || auth()->user()->manager->status !== 'active')
    <div class="alert error">
        <strong>You cannot submit a leave request yet.</strong>
        <div>Your account does not have a valid active Manager assigned. Please contact an Administrator and ask them to assign an active Manager to your account.</div>
    </div>
@endif

<div class="form-card">
    <form method="POST" action="{{ route('leave-requests.store') }}">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="leave_type_id">Leave Type</label>
                <select id="leave_type_id" name="leave_type_id" required>
                    <option value="">Select leave type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                            {{ $type->name }}
                            @if($type->tracks_balance)
                                · {{ auth()->user()->leaveBalances()->where('leave_type_id',$type->id)->where('year',now()->year)->first()?->allocated ?? $type->annual_allowance }} days/year
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="assigned_manager">Assigned Manager</label>
                <input id="assigned_manager" value="{{ auth()->user()->manager?->name ?? 'No Manager assigned' }}" disabled>
            </div>

            <div class="field">
                <label for="start_date">Start Date</label>
                <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" min="{{ now()->toDateString() }}" required>
            </div>

            <div class="field">
                <label for="end_date">End Date</label>
                <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" min="{{ now()->toDateString() }}" required>
            </div>

            <div class="field full-width">
                <label for="reason">Reason</label>
                <textarea id="reason" name="reason" rows="5" maxlength="1000" required placeholder="Briefly explain the reason for your leave...">{{ old('reason') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('leave-requests.index') }}">Cancel</a>
            <button class="button primary" type="submit" @disabled(!auth()->user()->manager || !auth()->user()->manager->isManager() || auth()->user()->manager->status !== 'active')>Submit Request</button>
        </div>
    </form>
</div>
@endsection

@extends('layout')
@section('title', 'New Request')
@section('content')
<h1>New leave request</h1>
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('leave.store') }}">
        @csrf
        <label for="leave_type_id">Leave type</label>
        <select id="leave_type_id" name="leave_type_id" required>
            @foreach($types as $t)
                <option value="{{ $t->id }}" @selected(old('leave_type_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>

        <label for="start_date">Start date</label>
        <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" min="{{ now()->toDateString() }}" required>

        <label for="end_date">End date</label>
        <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" min="{{ now()->toDateString() }}" required>
        <div class="muted" id="days-hint">Weekends and holidays are not counted.</div>

        <label for="reason">Reason (optional)</label>
        <textarea id="reason" name="reason" rows="3" maxlength="500">{{ old('reason') }}</textarea>

        <p><button>Submit request</button> <a class="btn btn-light" href="{{ route('dashboard') }}">Back</a></p>
    </form>
</div>
<script>
    // Live working-day estimate (server re-validates on submit)
    const s = document.getElementById('start_date'), e = document.getElementById('end_date'), h = document.getElementById('days-hint');
    function upd() {
        if (!s.value || !e.value || e.value < s.value) return;
        let d = new Date(s.value + 'T00:00:00'), end = new Date(e.value + 'T00:00:00'), n = 0;
        for (; d <= end; d.setDate(d.getDate() + 1)) { if (d.getDay() % 6 !== 0) n++; }
        h.textContent = n + ' working day(s) (weekends excluded; holidays checked on submit).';
    }
    s.addEventListener('change', () => { if (!e.value || e.value < s.value) e.value = s.value; upd(); });
    e.addEventListener('change', upd); upd();
</script>
@endsection

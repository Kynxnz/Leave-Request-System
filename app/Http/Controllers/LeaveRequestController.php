<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(private LeaveService $leave) {}
    public function index(Request $request)
    {
        $user=auth()->user(); $q=LeaveRequest::with(['user','manager','type']);
        if($user->isStaff()) $q->where('user_id',$user->id); elseif($user->isManager()) $q->where('manager_id',$user->id);
        if($request->filled('status')) $q->where('status',$request->string('status'));
        if($request->filled('search')) $q->whereHas('user',fn($x)=>$x->where('name','like','%'.$request->string('search').'%'));
        $requests=$q->latest()->paginate(12)->withQueryString();
        return view('leave-requests.index',compact('requests'));
    }
    public function create()
    { abort_unless(auth()->user()->isStaff(),403); return view('leave-requests.create',['types'=>LeaveType::where('is_active',true)->orderBy('name')->get()]); }
    public function store(StoreLeaveRequest $request)
    {
        $this->leave->submit($request->user(),LeaveType::findOrFail($request->integer('leave_type_id')),Carbon::parse($request->start_date),Carbon::parse($request->end_date),$request->string('reason')->toString());
        return redirect()->route('leave-requests.index')->with('success','Leave request submitted successfully and sent to your assigned Manager.');
    }
    public function show(LeaveRequest $leaveRequest)
    {
        $user=auth()->user(); abort_unless($user->isAdmin() || ($user->isManager() && $leaveRequest->manager_id===$user->id) || ($user->isStaff() && $leaveRequest->user_id===$user->id),403);
        return view('leave-requests.show',['leaveRequest'=>$leaveRequest->load(['user','manager','type','approvals.approver'])]);
    }
    public function cancel(LeaveRequest $leaveRequest)
    { abort_unless($leaveRequest->user_id===auth()->id() && $leaveRequest->isCancellable(),403); $this->leave->cancel($leaveRequest); return back()->with('success','Leave request cancelled.'); }
}

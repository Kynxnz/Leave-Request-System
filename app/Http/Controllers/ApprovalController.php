<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isManager(),403);
        $q=LeaveRequest::with(['user','type'])->where('manager_id',auth()->id());
        if($request->filled('status')) $q->where('status',$request->string('status'));
        $requests=$q->latest()->paginate(12)->withQueryString();
        return view('approvals.index',compact('requests'));
    }
    public function approve(Request $request, LeaveRequest $leaveRequest, LeaveService $service)
    { abort_unless($leaveRequest->manager_id===auth()->id() && $leaveRequest->status==='pending',403); $request->validate(['remarks'=>['nullable','string','max:1000']]); $service->decide($leaveRequest,auth()->user(),'approved',$request->remarks); return back()->with('success','Leave request approved successfully.'); }
    public function reject(Request $request, LeaveRequest $leaveRequest, LeaveService $service)
    { abort_unless($leaveRequest->manager_id===auth()->id() && $leaveRequest->status==='pending',403); $data=$request->validate(['remarks'=>['required','string','max:1000']]); $service->decide($leaveRequest,auth()->user(),'rejected',$data['remarks']); return back()->with('success','Leave request rejected.'); }
}

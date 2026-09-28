<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(LeaveService $leave)
    {
        $user=Auth::user();
        if ($user->isAdmin()) {
            $stats=['staff'=>User::where('role','Staff')->where('status','active')->count(),'managers'=>User::where('role','Manager')->where('status','active')->count(),'pending'=>LeaveRequest::where('status','pending')->count(),'approved'=>LeaveRequest::where('status','approved')->count()];
            $recent=LeaveRequest::with(['user','type','manager'])->latest()->limit(8)->get();
        } elseif ($user->isManager()) {
            $base=LeaveRequest::where('manager_id',$user->id); $stats=['staff'=>$user->reports()->where('status','active')->count(),'managers'=>null,'pending'=>(clone $base)->where('status','pending')->count(),'approved'=>(clone $base)->where('status','approved')->count()];
            $recent=$base->with(['user','type'])->latest()->limit(8)->get();
        } else {
            $base=$user->leaveRequests(); $stats=['staff'=>null,'managers'=>null,'pending'=>(clone $base)->where('status','pending')->count(),'approved'=>(clone $base)->where('status','approved')->count()];
            $recent=$base->with(['type','manager'])->latest()->limit(8)->get();
        }
        $balance=$user->isStaff()?collect($user->leaveBalances()->with('type')->where('year',now()->year)->get())->map(fn($b)=>['type'=>$b->type,'available'=>max(0,$b->allocated-$b->used),'allocated'=>$b->allocated,'used'=>$b->used]):collect();
        return view('dashboard',compact('user','stats','recent','balance'));
    }
}

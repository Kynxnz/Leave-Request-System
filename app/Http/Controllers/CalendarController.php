<?php
namespace App\Http\Controllers;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month=Carbon::createFromFormat('Y-m-d',($request->query('month',now()->format('Y-m')).'-01'))->startOfMonth();
        $start=$month->copy()->startOfWeek(Carbon::MONDAY); $end=$month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $q=LeaveRequest::with(['user','type'])->where('status','approved')->whereDate('start_date','<=',$end)->whereDate('end_date','>=',$start);
        $u=auth()->user(); if($u->isStaff()) $q->where('user_id',$u->id); elseif($u->isManager()) $q->where('manager_id',$u->id);
        $leaves=$q->get(); $days=[];
        for($d=$start->copy();$d->lte($end);$d->addDay()) $days[]=['date'=>$d->copy(),'in_month'=>$d->month===$month->month,'leaves'=>$leaves->filter(fn($l)=>$l->start_date->lte($d)&&$l->end_date->gte($d))];
        return view('calendar.index',['month'=>$month,'weeks'=>array_chunk($days,7),'prev'=>$month->copy()->subMonth()->format('Y-m'),'next'=>$month->copy()->addMonth()->format('Y-m')]);
    }
}

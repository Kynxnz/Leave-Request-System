<?php
namespace App\Http\Controllers;
use App\Models\LeaveRequest;
use App\Models\User;
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
        return view('calendar.index',['month'=>$month,'weeks'=>array_chunk($days,7),'prev'=>$month->copy()->subMonth()->format('Y-m'),'next'=>$month->copy()->addMonth()->format('Y-m'),'feedUrl'=>route('calendar.feed',auth()->user()->calendar_token)]);
    }
    public function feed(string $token)
    {
        $user=User::where('calendar_token',$token)->firstOrFail(); $leaves=$user->leaveRequests()->with('type')->where('status','approved')->get();
        $lines=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Leave Request System//EN','CALSCALE:GREGORIAN','X-WR-CALNAME:My Approved Leave'];
        foreach($leaves as $l){$lines=array_merge($lines,['BEGIN:VEVENT','UID:leave-'.$l->id.'@leave-system','DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),'DTSTART;VALUE=DATE:'.$l->start_date->format('Ymd'),'DTEND;VALUE=DATE:'.$l->end_date->copy()->addDay()->format('Ymd'),'SUMMARY:'.$l->type->name.' Leave','END:VEVENT']);}
        return response(implode("\r\n",[...$lines,'END:VCALENDAR'])."\r\n",200,['Content-Type'=>'text/calendar; charset=utf-8']);
    }
}

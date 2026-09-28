<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestApproval;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveApprovalNeeded;
use App\Notifications\LeaveRequestDecided;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $holidays = config('leave.holidays', []); $count = 0;
        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $day) {
            if (!$day->isWeekend() && !in_array($day->toDateString(), $holidays, true)) $count++;
        }
        return $count;
    }

    public function balanceFor(User $user, LeaveType $type, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            ['user_id'=>$user->id,'leave_type_id'=>$type->id,'year'=>$year],
            ['allocated'=>$type->annual_allowance,'used'=>0]
        );
    }

    public function balanceSummary(User $user, LeaveType $type, int $year): array
    {
        if (!$type->tracks_balance) return ['allocated'=>0,'used'=>0,'pending'=>0,'available'=>null];
        $balance = $this->balanceFor($user,$type,$year);
        $pending = (int) LeaveRequest::where('user_id',$user->id)->where('leave_type_id',$type->id)->where('status','pending')->whereYear('start_date',$year)->sum('days');
        return ['allocated'=>$balance->allocated,'used'=>$balance->used,'pending'=>$pending,'available'=>max(0,$balance->allocated-$balance->used-$pending)];
    }

    public function validateBusinessRules(User $user, LeaveType $type, Carbon $start, Carbon $end): array
    {
        $errors=[]; $days=$this->countWorkingDays($start,$end);
        if ($start->year !== $end->year) $errors['end_date']='Leave must be within one calendar year.';
        if ($days < 1) $errors['start_date']='Selected dates contain no working days.';
        if (!$user->manager_id || !$user->manager || !$user->manager->isManager() || $user->manager->status !== 'active') $errors['manager']='Your account has no valid active assigned Manager. Contact an Administrator.';
        $conflict=LeaveRequest::where('user_id',$user->id)->overlapping($start->toDateString(),$end->toDateString())->first();
        if ($conflict) $errors['start_date']='These dates overlap an existing leave request.';
        if ($type->tracks_balance) {
            $available=$this->balanceSummary($user,$type,$start->year)['available'];
            if ($days>$available) $errors['leave_type_id']="Insufficient {$type->name} balance. Available: {$available} day(s).";
        }
        return $errors;
    }

    public function submit(User $user, LeaveType $type, Carbon $start, Carbon $end, string $reason): LeaveRequest
    {
        $errors=$this->validateBusinessRules($user,$type,$start,$end);
        if ($errors) throw \Illuminate\Validation\ValidationException::withMessages($errors);
        return DB::transaction(function() use($user,$type,$start,$end,$reason) {
            $request=LeaveRequest::create([
                'user_id'=>$user->id,'leave_type_id'=>$type->id,'manager_id'=>$user->manager_id,
                'start_date'=>$start->toDateString(),'end_date'=>$end->toDateString(),
                'days'=>$this->countWorkingDays($start,$end),'reason'=>$reason,'status'=>'pending'
            ]);
            $request->approvals()->create(['approver_id'=>$user->manager_id,'action'=>'submitted','previous_status'=>null,'new_status'=>'pending','remarks'=>null,'acted_at'=>now()]);
            $user->manager->notify(new LeaveApprovalNeeded($request));
            return $request;
        });
    }

    public function decide(LeaveRequest $request, User $manager, string $decision, ?string $remarks): void
    {
        DB::transaction(function() use($request,$manager,$decision,$remarks) {
            $request->refresh();
            abort_unless($request->status==='pending' && $request->manager_id===$manager->id && $manager->isManager(),403);
            if ($decision==='rejected') abort_if(blank($remarks),422,'A rejection reason is required.');
            $old=$request->status;
            $request->update(['status'=>$decision,'rejection_reason'=>$decision==='rejected'?$remarks:null,'decided_at'=>now()]);
            if ($decision==='approved' && $request->type->tracks_balance) {
                $balance=$this->balanceFor($request->user,$request->type,$request->start_date->year);
                abort_if($balance->allocated-$balance->used < $request->days,422,'The leave balance is no longer sufficient.');
                $balance->increment('used',$request->days);
            }
            $request->approvals()->create(['approver_id'=>$manager->id,'action'=>$decision,'previous_status'=>$old,'new_status'=>$decision,'remarks'=>$remarks,'acted_at'=>now()]);
            $request->user->notify(new LeaveRequestDecided($request,$decision,$manager,$remarks));
        });
    }

    public function cancel(LeaveRequest $request): void
    {
        DB::transaction(function() use($request) {
            $request->refresh(); abort_unless($request->status==='pending',422);
            $request->update(['status'=>'cancelled','decided_at'=>now()]);
            $request->approvals()->create(['approver_id'=>$request->user_id,'action'=>'cancelled','previous_status'=>'pending','new_status'=>'cancelled','remarks'=>'Cancelled by Staff','acted_at'=>now()]);
        });
    }
}

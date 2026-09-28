<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestApproval extends Model
{
    protected $fillable = ['leave_request_id','approver_id','action','previous_status','new_status','remarks','acted_at'];
    protected $casts = ['acted_at'=>'datetime'];
    public function leaveRequest(): BelongsTo { return $this->belongsTo(LeaveRequest::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_id'); }
}

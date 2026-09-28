<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id','leave_type_id','manager_id','start_date','end_date','days','reason','status','rejection_reason','decided_at'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','decided_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function type(): BelongsTo { return $this->belongsTo(LeaveType::class, 'leave_type_id'); }
    public function approvals(): HasMany { return $this->hasMany(LeaveRequestApproval::class)->latest(); }
    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    { return $query->whereIn('status',['pending','approved'])->whereDate('start_date','<=',$end)->whereDate('end_date','>=',$start); }
    public function isCancellable(): bool { return $this->status === 'pending' && $this->end_date->gte(today()); }
}

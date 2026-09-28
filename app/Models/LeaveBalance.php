<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = ['user_id', 'leave_type_id', 'year', 'allocated', 'used'];
    protected $casts = ['year' => 'integer', 'allocated' => 'integer', 'used' => 'integer'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function type(): BelongsTo { return $this->belongsTo(LeaveType::class, 'leave_type_id'); }
}

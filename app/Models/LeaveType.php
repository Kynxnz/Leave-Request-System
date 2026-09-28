<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = ['name', 'description', 'color', 'annual_allowance', 'tracks_balance', 'is_active'];
    protected $casts = ['tracks_balance' => 'boolean', 'is_active' => 'boolean'];
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function balances(): HasMany { return $this->hasMany(LeaveBalance::class); }
}

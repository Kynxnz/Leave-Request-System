<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'manager_id', 'status'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    protected static function booted(): void
    {
        static::creating(fn (User $user) => $user->calendar_token ??= Str::random(40));
    }

    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function reports(): HasMany { return $this->hasMany(User::class, 'manager_id'); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function leaveBalances(): HasMany { return $this->hasMany(LeaveBalance::class); }

    public function isAdmin(): bool { return $this->role === 'Administrator'; }
    public function isManager(): bool { return $this->role === 'Manager'; }
    public function isStaff(): bool { return $this->role === 'Staff'; }

    public function pendingApprovalsCount(): int
    {
        if (!$this->isManager()) return 0;
        return LeaveRequest::where('manager_id', $this->id)->where('status', 'pending')->count();
    }
}

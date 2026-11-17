<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['member_id', 'membership_plan_id', 'start_date', 'end_date', 'amount', 'discount', 'status'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'amount' => 'decimal:2', 'discount' => 'decimal:2'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function netAmount(): float
    {
        return (float) $this->amount - (float) $this->discount;
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function dueAmount(): float
    {
        return max(0, $this->netAmount() - $this->paidAmount());
    }

    public function daysLeft(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date, false));
    }

    /** Mark all past-due active subscriptions as expired. */
    public static function expireOld(): int
    {
        return static::where('status', 'active')->whereDate('end_date', '<', now()->toDateString())->update(['status' => 'expired']);
    }
}

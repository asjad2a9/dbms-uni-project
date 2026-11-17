<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_code', 'name', 'email', 'phone', 'gender', 'dob', 'address', 'emergency_contact',
        'height_cm', 'weight_kg', 'trainer_id', 'joined_at', 'status', 'notes',
    ];

    protected $casts = ['dob' => 'date', 'joined_at' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Member $m) {
            if (empty($m->member_code)) {
                $next = (static::max('id') ?? 0) + 1;
                $m->member_code = 'GYM-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->latest('end_date');
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latestOfMany('end_date');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class)->latest('date');
    }

    public function bmi(): ?float
    {
        if (! $this->height_cm || ! $this->weight_kg) return null;
        $h = $this->height_cm / 100;
        return round($this->weight_kg / ($h * $h), 1);
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
    }
}

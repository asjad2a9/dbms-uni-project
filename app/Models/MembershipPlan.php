<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $fillable = ['name', 'duration_days', 'price', 'description', 'features', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function featureList(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->features))));
    }

    public function durationLabel(): string
    {
        $d = $this->duration_days;
        if ($d % 365 === 0) return ($d / 365) . ' Year' . ($d > 365 ? 's' : '');
        if ($d % 30 === 0) return ($d / 30) . ' Month' . ($d > 30 ? 's' : '');
        return $d . ' Days';
    }
}

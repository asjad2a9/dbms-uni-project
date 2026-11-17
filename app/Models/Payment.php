<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['invoice_no', 'member_id', 'subscription_id', 'amount', 'method', 'paid_at', 'reference', 'notes', 'received_by'];

    protected $casts = ['paid_at' => 'date', 'amount' => 'decimal:2'];

    public const METHODS = ['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'online' => 'Online'];

    protected static function booted(): void
    {
        static::creating(function (Payment $p) {
            if (empty($p->invoice_no)) {
                $next = (static::max('id') ?? 0) + 1;
                $p->invoice_no = 'INV-' . now()->format('Ym') . '-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}

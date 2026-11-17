<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['title', 'category', 'amount', 'spent_on', 'notes'];

    protected $casts = ['spent_on' => 'date', 'amount' => 'decimal:2'];

    public const CATEGORIES = ['rent', 'salary', 'equipment', 'utilities', 'maintenance', 'marketing', 'other'];
}

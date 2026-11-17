<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'gender', 'specialization', 'experience_years', 'salary', 'joined_at', 'bio', 'status'];

    protected $casts = ['joined_at' => 'date', 'salary' => 'decimal:2'];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}

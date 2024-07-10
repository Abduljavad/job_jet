<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeofCurrency(Builder $query, $request)
    {
        if ($request->has('currency')) {
            return $query->where('currency', 'LIKE', $request->currency)->orWhere('is_trial',true);
        }
    }
}

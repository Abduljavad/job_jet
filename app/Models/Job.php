<?php

namespace App\Models;

use App\Http\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'job_applications';
    
    protected $casts = [
        'image' => 'json',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_category', 'job_id', 'category_id');
    }

    public function scopeFilter($query, QueryFilter $filters)
    {
        return $filters->apply($query);
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }
}

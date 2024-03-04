<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'image' => 'json'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class,'job_category','job_id','category_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'image' => 'json',
        'icon' => 'json',
    ];


    public function jobs()
    {
        return $this->belongsToMany(Category::class,'job_category','category_id','job_id');
    }
}

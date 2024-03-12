<?php

namespace App\Http\Filters;

class JobFilter extends QueryFilter
{
    public function sort($order = 'desc')
    {
        return $this->builder->orderBy('created_at', $order);
    }

    public function categories($ids = null)
    {
        $categoryIds = explode(',', $ids);

        return $this->builder->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('id', $categoryIds);
        });
    }

    public function search($name = null)
    {
        return $this->builder->where('job_title', 'like', '%'.$name.'%');
    }

    public function location($location = null)
    {
        $locationIds = explode(',', $location);

        return $this->builder->whereHas('location', function ($query) use ($locationIds) {
            $query->whereIn('id', $locationIds);
        });
    }
}

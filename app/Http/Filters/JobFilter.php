<?php

namespace App\Http\Filters;

use Carbon\Carbon;

class JobFilter extends QueryFilter
{
    public function sort($sort = 'desc')
    {
        return $this->builder->orderBy('created_at', $sort);
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

    public function status($status = null)
    {
        return $this->builder->where('status', $status);
    }

    public function date_filter($sort = null)
    {
        switch ($sort) {
            case 'today':
                return $this->builder->whereDate('created_at', Carbon::today());
            case 'yesterday':
                return $this->builder->whereDate('created_at', Carbon::yesterday());
            case 'last-week':
                return $this->builder->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            case 'last-month':
                return $this->builder->whereDate('created_at', '>=', Carbon::now()->subMonth());
            case 'last-2-months':
                return $this->builder->whereDate('created_at', '>=', Carbon::now()->subMonths(2));
            default:
                break;
        }
    }

    public function joining_time($joiningTime)
    {
        return $this->builder->where('joining_time', 'like', '%'.$joiningTime.'%');
    }

    public function salary($salary)
    {
        return $this->builder->where('salary', 'like', '%'.$salary.'%');
    }

    public function experience_required($experience_required = null)
    {
        return $this->builder->where('experience_required', 'like', '%'.$experience_required.'%');
    }
}

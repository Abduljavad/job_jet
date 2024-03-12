<?php

namespace App\Http\Controllers;

use App\Events\JobCreatedEvent;
use App\Http\Filters\JobFilter;
use App\Http\Filters\QueryFilter;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('isSuperAdmin')->except([
            'subscriptionBasedJobList', 'show',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function subscriptionBasedJobList(Request $request, QueryFilter $filters)
    {
        $userSubscriptionExist = auth()->user()->user_subscriptions()->latest()->first();
        if (! $userSubscriptionExist) {
            return $this->errorResponse("user doesn't have active subscription", 403);
        }

        return Job::with(['categories', 'location'])->whereDate('created_at', '<=', $userSubscriptionExist->end_date)->paginate($request->input('limit', 20));
    }

    public function index(Request $request, JobFilter $filters)
    {
        return Job::with(['categories', 'location'])->filter($filters)->paginate($request->input('limit', 20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobRequest $request)
    {
        $job = Job::create($request->validated());
        if ($request->categories) {
            $job->categories()->sync($request->categories);
        }
        event(new JobCreatedEvent($job));

        return $this->successResponse('job created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job)
    {
        return $job->load(['categories']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobRequest $request, Job $job)
    {
        $job->update($request->validated());
        $job->categories()->sync($request->categories);

        return $this->successResponse('job updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return $this->successResponse('job deleted successfully');
    }
}

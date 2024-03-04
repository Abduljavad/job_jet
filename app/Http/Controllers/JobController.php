<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Job::paginate($request->input('limit', 20));
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

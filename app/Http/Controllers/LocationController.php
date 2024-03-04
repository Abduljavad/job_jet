<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Location::get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request)
    {
        Location::create($request->validated());
        return $this->successResponse('location created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        return $location;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $location->update($request->validated());
        return $this->successResponse('location updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(location $location)
    {
        $location->delete();
        return $this->successResponse('location deleted successfully');
    }
}

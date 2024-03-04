<?php

use App\Models\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->foreignIdFor(Location::class)->nullable();
            $table->text('content')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('image')->nullable();
            $table->string('experience_required')->nullable();
            $table->string('salary')->nullable();
            $table->string('joining_time')->nullable();
            $table->boolean('is_active')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};

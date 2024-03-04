<?php

use App\Models\User;
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
        Schema::create('stripe_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('payment_intent_id')->nullable();
            $table->string('subscription_id')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->nullable();
            $table->string('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_intent_id');
            $table->index('subscription_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_transactions');
    }
};

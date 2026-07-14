<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('points');
            $table->string('reason')->default('signup_bonus');
            $table->timestamps();

            $table->index('referrer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_earnings');
    }
};

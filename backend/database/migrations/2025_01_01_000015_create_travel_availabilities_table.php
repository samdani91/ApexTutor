<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('travel_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('district_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->boolean('open_to_tuitions')->default(true);
            $table->text('notes')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
            $table->foreign('district_id')->references('id')->on('districts');
            $table->index(['from_date', 'to_date'], 'idx_dates');
            $table->index('district_id');
        });
    }
    public function down(): void { Schema::dropIfExists('travel_availabilities'); }
};

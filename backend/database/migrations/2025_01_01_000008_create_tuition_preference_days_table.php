<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_preference_days', function (Blueprint $table) {
            $table->foreignId('tuition_preference_id')->constrained()->cascadeOnDelete();
            $table->enum('day', ['sat','sun','mon','tue','wed','thu','fri']);
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->primary(['tuition_preference_id', 'day']);
        });
    }
    public function down(): void { Schema::dropIfExists('tuition_preference_days'); }
};

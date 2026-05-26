<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_preference_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuition_preference_id')->constrained()->cascadeOnDelete();
            $table->string('area_name', 150);
        });
    }
    public function down(): void { Schema::dropIfExists('tuition_preference_locations'); }
};

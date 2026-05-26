<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tutor_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('relation', ['father','mother','sibling','spouse','friend','relative','other']);
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tutor_emergency_contacts'); }
};

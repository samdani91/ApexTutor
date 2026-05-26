<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('connection_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained('tuition_requirements')->nullOnDelete();
            $table->enum('status', ['pending','admin_reviewing','tutor_contacted','connected','declined','closed'])->default('pending');
            $table->text('guardian_message')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('connection_requests'); }
};

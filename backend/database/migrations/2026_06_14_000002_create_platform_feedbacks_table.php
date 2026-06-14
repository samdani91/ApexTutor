<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('platform_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('quote');
            $table->string('display_label');
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('show_on_landing')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('platform_feedbacks'); }
};

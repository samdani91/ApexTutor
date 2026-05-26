<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 191)->unique();
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->enum('role', ['tutor','guardian','student','admin','super_admin'])->default('guardian');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('avatar', 500)->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->index('role');
            $table->index('is_active');
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};

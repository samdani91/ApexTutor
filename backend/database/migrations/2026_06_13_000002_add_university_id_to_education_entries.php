<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('education_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('university_id')->nullable()->after('institute_name');
            $table->foreign('university_id')->references('id')->on('universities')->nullOnDelete();
            $table->string('institute_name', 255)->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('education_entries', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn('university_id');
            $table->string('institute_name', 255)->nullable(false)->change();
        });
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('subjects', function (Blueprint $table) {
            // Science / Business Studies / Humanities. NULL = compulsory or
            // cross-group subject, shown regardless of the group filter.
            $table->string('group', 30)->nullable()->after('medium');
        });
    }

    public function down(): void {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};

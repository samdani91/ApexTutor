<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('guardian_profiles', function (Blueprint $table) {
            $table->string('occupation', 100)->nullable()->after('account_type');
            $table->string('relationship_to_student', 50)->nullable()->after('occupation');
            $table->string('nid_number', 30)->nullable()->after('relationship_to_student');
            $table->string('nid_document', 255)->nullable()->after('nid_number');
        });
    }

    public function down(): void {
        Schema::table('guardian_profiles', function (Blueprint $table) {
            $table->dropColumn(['occupation', 'relationship_to_student', 'nid_number', 'nid_document']);
        });
    }
};

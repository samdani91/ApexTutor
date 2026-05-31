<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // tuition_preference_locations — area_id is queried in searches
        Schema::table('tuition_preference_locations', function (Blueprint $table) {
            if (!$this->hasIndex('tuition_preference_locations', 'tuition_preference_locations_area_id_index')) {
                $table->index('area_id');
            }
        });

        // connection_requests — frequently filtered by status
        Schema::table('connection_requests', function (Blueprint $table) {
            if (!$this->hasIndex('connection_requests', 'connection_requests_status_index')) {
                $table->index('status');
            }
        });

        // notifications — frequently queried by notifiable + read_at
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->hasIndex('notifications', 'notifications_notifiable_read_at_index')) {
                $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
            }
        });

        // tutor_profiles — profile_completion_percent used in ORDER BY
        Schema::table('tutor_profiles', function (Blueprint $table) {
            if (!$this->hasIndex('tutor_profiles', 'tutor_profiles_completion_verified_index')) {
                $table->index(['is_verified', 'profile_completion_percent']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tuition_preference_locations', fn($t) => $t->dropIndex(['area_id']));
        Schema::table('tutor_profiles', fn($t) => $t->dropIndex(['is_verified', 'profile_completion_percent']));
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains('Key_name', $index);
    }
};

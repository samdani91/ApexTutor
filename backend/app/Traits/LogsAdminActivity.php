<?php
namespace App\Traits;

use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

trait LogsAdminActivity
{
    protected function logActivity(Request $request, string $action, string $targetType, int $targetId, string $description): void
    {
        AdminActivityLog::create([
            'admin_id'    => $request->user()->id,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'description' => $description,
            'ip_address'  => $request->ip(),
        ]);
    }
}

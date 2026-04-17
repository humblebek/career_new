<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(protected Request $request) {}

    public function log(
        string $action,
        ?int $userId = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetLabel = null,
    ): void {
        AuditLog::create([
            'user_id'      => $userId,
            'action'       => $action,
            'target_type'  => $targetType,
            'target_id'    => $targetId,
            'target_label' => $targetLabel,
            'ip_address'   => $this->request->ip(),
            'user_agent'   => $this->request->userAgent(),
        ]);
    }
}
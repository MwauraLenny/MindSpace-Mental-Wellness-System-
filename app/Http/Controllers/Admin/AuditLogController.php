<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->string('action')->toString();

        $logs = AuditLog::query()
            ->with('actor')
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->orderByDesc('performed_at')
            ->paginate(25)
            ->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'selectedAction' => $action,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalAuditController extends Controller
{
    public function index(): View
    {
        $auditLogs = AuditLog::with(['user', 'auditable'])
            ->latest()
            ->paginate(15);

        return view('audit.index', compact('auditLogs'));
    }
}

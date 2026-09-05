<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use Illuminate\View\View;

class AdminAuditController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.audit.index', [
            'logs' => PlatformAuditLog::with(['user', 'business'])->latest()->paginate(20),
        ]);
    }
}

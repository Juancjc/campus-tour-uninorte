<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\AdminAuditLog;
use App\Services\ReportService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReportFilterRequest $request, ReportService $reports): Response
    {
        $filters = $request->validated();
        AdminAuditLog::query()->create([
            'admin_id' => $request->user()->id,
            'action' => 'viewed_dashboard',
            'entity' => 'admin_dashboard',
            'ip_address' => $request->ip(),
            'metadata' => ['filters' => $filters],
        ]);

        return Inertia::render('Admin/Dashboard', [
            ...$reports->dashboard($filters),
            'filters' => $filters,
        ]);
    }
}

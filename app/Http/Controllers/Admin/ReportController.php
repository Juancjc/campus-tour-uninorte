<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\Profession;
use App\Services\ReportService;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, ReportService $reports): Response
    {
        $filters = $request->validated();

        return Inertia::render('Admin/Reports', [
            ...$reports->reports($filters),
            'filters' => $filters,
            'professionsFilter' => Profession::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}

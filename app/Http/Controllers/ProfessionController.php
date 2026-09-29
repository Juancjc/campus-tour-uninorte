<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = $request->string('q')->trim()->toString();

        return response()->json(
            Profession::query()
                ->where('active', true)
                ->when($term, fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
                ->orderBy('category')
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'category']),
        );
    }
}

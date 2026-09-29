<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'games' => Game::query()->where('active', true)->orderBy('display_order')->get(['slug', 'name', 'category', 'description', 'icon']),
            'participants' => User::query()->where('is_admin', false)->count(),
            'appUrl' => config('app.url'),
        ]);
    }
}

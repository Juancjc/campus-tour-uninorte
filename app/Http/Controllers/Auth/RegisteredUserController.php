<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\Profession;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'professions' => Profession::query()->where('active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterUserRequest $request, RegisterUserAction $register): RedirectResponse
    {
        $user = $register->handle($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Cadastro concluído! Escolha seu primeiro desafio.');
    }
}

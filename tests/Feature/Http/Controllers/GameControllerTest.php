<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_404_when_game_is_inactive(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['slug' => 'rede-em-acao', 'active' => false]);

        $response = $this->actingAs($user)->get(route('games.show', $game));

        $response->assertNotFound();
    }
}

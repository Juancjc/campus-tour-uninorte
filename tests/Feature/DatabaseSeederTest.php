<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Game;
use App\Models\Profession;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_seeds_the_campus_tour_catalog(): void
    {
        $this->seed();

        $this->assertSame(3, Game::query()->count());
        $this->assertSame(2, Game::query()->where('active', true)->count());
        $this->assertFalse(Game::query()->where('slug', 'rede-em-acao')->value('active'));
        $this->assertGreaterThan(10, Profession::query()->count());
        $this->assertGreaterThanOrEqual(6, Achievement::query()->count());
    }
}

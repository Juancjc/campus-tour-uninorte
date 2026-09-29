<?php

namespace Tests\Unit\Services;

use App\Services\GameCatalog;
use PHPUnit\Framework\TestCase;

class GameCatalogTest extends TestCase
{
    public function test_repeat_reuses_previous_command_and_reaches_goal(): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner([
            'forward',
            'repeat',
            'repeat',
            'repeat',
            'left',
            'forward',
            'repeat',
            'repeat',
            'repeat',
        ], 5000);

        $this->assertTrue($result['completed']);
        $this->assertSame(990, $result['score']);
        $this->assertSame(['x' => 4, 'y' => 0, 'direction' => 'north'], $result['final']);
    }

    public function test_right_command_rotates_robot_and_reaches_goal(): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner([
            'forward',
            'forward',
            'forward',
            'forward',
            'right',
            'right',
            'right',
            'forward',
            'forward',
            'forward',
            'forward',
        ], 5000);

        $this->assertTrue($result['completed']);
        $this->assertSame(930, $result['score']);
        $this->assertSame(['x' => 4, 'y' => 0, 'direction' => 'north'], $result['final']);
    }
}

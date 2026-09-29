<?php

namespace Tests\Unit\Services;

use App\Services\GameCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GameCatalogTest extends TestCase
{
    /**
     * @param  list<string>  $commands
     * @param  array{x: int, y: int, direction: string}  $expectedFinalPosition
     */
    #[DataProvider('absoluteDirectionCommands')]
    public function test_direction_commands_move_one_cell_in_the_selected_direction(array $commands, array $expectedFinalPosition): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner($commands, 5000);

        $this->assertSame($expectedFinalPosition, $result['final']);
    }

    /**
     * @return array<string, array{list<string>, array{x: int, y: int, direction: string}}>
     */
    public static function absoluteDirectionCommands(): array
    {
        return [
            'up' => [['up'], ['x' => 0, 'y' => 3, 'direction' => 'north']],
            'down' => [['up', 'down'], ['x' => 0, 'y' => 4, 'direction' => 'south']],
            'left' => [['right', 'left'], ['x' => 0, 'y' => 4, 'direction' => 'west']],
            'right' => [['right'], ['x' => 1, 'y' => 4, 'direction' => 'east']],
        ];
    }

    public function test_simple_up_and_right_sequence_reaches_goal(): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner([
            'up',
            'up',
            'up',
            'up',
            'right',
            'right',
            'right',
            'right',
        ], 5000);

        $this->assertTrue($result['completed']);
        $this->assertSame(990, $result['score']);
        $this->assertSame(['x' => 4, 'y' => 0, 'direction' => 'east'], $result['final']);
    }

    public function test_obstacle_blocks_the_selected_movement(): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner(['right', 'up', 'up'], 5000);

        $this->assertFalse($result['completed']);
        $this->assertSame(['x' => 1, 'y' => 3, 'direction' => 'north'], $result['final']);
    }
}

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

    #[DataProvider('levelSolutions')]
    public function test_each_level_is_solvable_with_its_minimum_commands(int $level, array $commands): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner($commands, 5000, $level);

        $this->assertTrue($result['completed']);
        $this->assertSame($level, $result['level']);
    }

    /** @return array<string, array{int, list<string>}> */
    public static function levelSolutions(): array
    {
        return [
            'level 1' => [1, ['up', 'up', 'up', 'up', 'right', 'right', 'right', 'right']],
            'level 2' => [2, ['down', 'down', 'right', 'right', 'right', 'down', 'down', 'right']],
            'level 3' => [3, ['down', 'down', 'down', 'right', 'right', 'up', 'right', 'right', 'down', 'down', 'down', 'right']],
            'level 4' => [4, ['up', 'right', 'right', 'up', 'up', 'up', 'up', 'right', 'right', 'down', 'down', 'down', 'right', 'right', 'up', 'up', 'up', 'up']],
            'level 5' => [5, ['up', 'up', 'jump', 'up', 'right', 'right', 'right', 'right', 'right']],
        ];
    }

    public function test_level_five_cannot_be_solved_without_jumping_the_obstacle(): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner(['up', 'up', 'up', 'up', 'up', 'right', 'right', 'right', 'right', 'right'], 5000, 5);

        $this->assertFalse($result['completed']);
    }

    public function test_jump_is_ignored_when_there_is_no_obstacle_directly_ahead(): void
    {
        $result = (new GameCatalog)->evaluateCodeRunner(['jump'], 5000, 5);

        $this->assertSame(['x' => 0, 'y' => 5, 'direction' => 'north'], $result['final']);
    }
}

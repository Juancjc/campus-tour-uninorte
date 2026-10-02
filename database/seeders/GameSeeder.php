<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $games = [
            ['slug' => 'code-runner', 'name' => 'Code Runner', 'category' => 'Lógica e Programação', 'description' => 'Monte comandos, guie o robô e descubra como algoritmos funcionam.', 'icon' => 'pi pi-code', 'max_score' => 1000, 'display_order' => 1, 'active' => true],
            ['slug' => 'guardiao-digital', 'name' => 'Guardião Digital', 'category' => 'Segurança da Informação', 'description' => 'Identifique ameaças digitais e aprenda a navegar com segurança.', 'icon' => 'pi pi-shield', 'max_score' => 1000, 'display_order' => 2, 'active' => true],
            ['slug' => 'rede-em-acao', 'name' => 'Rede em Ação', 'category' => 'Redes e Internet', 'description' => 'Faça a mensagem atravessar a rede até o servidor e voltar.', 'icon' => 'pi pi-globe', 'max_score' => 1000, 'display_order' => 3, 'active' => false],
        ];

        foreach ($games as $game) {
            Game::query()->updateOrCreate(['slug' => $game['slug']], $game);
        }
    }
}

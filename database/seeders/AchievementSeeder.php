<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            ['slug' => 'primeiro-jogo', 'name' => 'Primeiro Jogo', 'description' => 'Concluiu o primeiro desafio.', 'icon' => 'pi pi-star', 'points' => 50],
            ['slug' => 'programador-iniciante', 'name' => 'Programador Iniciante', 'description' => 'Concluiu o Code Runner.', 'icon' => 'pi pi-code', 'points' => 100],
            ['slug' => 'mestre-da-logica', 'name' => 'Mestre da Lógica', 'description' => 'Fez 850 pontos ou mais no Code Runner.', 'icon' => 'pi pi-bolt', 'points' => 150],
            ['slug' => 'guardiao-digital', 'name' => 'Guardião Digital', 'description' => 'Concluiu o desafio de segurança.', 'icon' => 'pi pi-shield', 'points' => 100],
            ['slug' => 'explorador-da-tecnologia', 'name' => 'Explorador da Tecnologia', 'description' => 'Concluiu todos os desafios do Campus Tour.', 'icon' => 'pi pi-compass', 'points' => 250],
            ['slug' => 'top-10', 'name' => 'Top 10', 'description' => 'Entrou no Top 10 do ranking geral.', 'icon' => 'pi pi-trophy', 'points' => 200],
        ];

        foreach ($achievements as $achievement) {
            Achievement::query()->updateOrCreate(['slug' => $achievement['slug']], [...$achievement, 'active' => true]);
        }
    }
}

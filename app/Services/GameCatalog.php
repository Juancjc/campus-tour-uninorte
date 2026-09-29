<?php

namespace App\Services;

use App\Models\Game;
use InvalidArgumentException;

class GameCatalog
{
    /** @return array<string, mixed> */
    public function publicConfig(Game|string $game): array
    {
        $slug = $game instanceof Game ? $game->slug : $game;
        $config = $this->config($slug);

        if ($slug === 'code-runner') {
            return $config;
        }

        return [
            'items' => collect($config['items'])
                ->map(fn (array $item): array => collect($item)->except(['answer', 'explanation'])->all())
                ->values()
                ->all(),
            'total' => count($config['items']),
        ];
    }

    /** @return array{correct: bool, points: int, explanation: string} */
    public function evaluateAnswer(string $slug, string $itemId, mixed $answer): array
    {
        $item = collect($this->config($slug)['items'] ?? [])->firstWhere('id', $itemId);

        if (! $item) {
            throw new InvalidArgumentException('Desafio não encontrado.');
        }

        $correct = is_array($item['answer'])
            ? is_array($answer) && array_values($answer) === array_values($item['answer'])
            : $answer === $item['answer'];

        return [
            'correct' => $correct,
            'points' => $correct ? (int) $item['points'] : 0,
            'explanation' => $item['explanation'],
        ];
    }

    /** @return array{completed: bool, score: int, final: array{x: int, y: int, direction: string}, minimum_commands: int} */
    public function evaluateCodeRunner(array $commands, int $durationMs): array
    {
        $config = $this->config('code-runner');
        $position = $config['start'];
        $direction = $config['direction'];
        $obstacles = collect($config['obstacles'])->map(fn (array $point): string => implode(':', $point))->all();
        $expanded = [];

        foreach (array_slice($commands, 0, 30) as $command) {
            if ($command === 'repeat' && $expanded !== []) {
                $expanded[] = end($expanded);
            } elseif (in_array($command, ['forward', 'left', 'right'], true)) {
                $expanded[] = $command;
            }
        }

        foreach ($expanded as $command) {
            if ($command === 'left' || $command === 'right') {
                $directions = ['north', 'east', 'south', 'west'];
                $current = array_search($direction, $directions, true);
                $offset = $command === 'right' ? 1 : 3;
                $direction = $directions[($current + $offset) % 4];

                continue;
            }

            $next = match ($direction) {
                'north' => ['x' => $position['x'], 'y' => $position['y'] - 1],
                'east' => ['x' => $position['x'] + 1, 'y' => $position['y']],
                'south' => ['x' => $position['x'], 'y' => $position['y'] + 1],
                default => ['x' => $position['x'] - 1, 'y' => $position['y']],
            };

            $insideBoard = $next['x'] >= 0 && $next['x'] < $config['board_size'] && $next['y'] >= 0 && $next['y'] < $config['board_size'];
            if ($insideBoard && ! in_array($next['x'].':'.$next['y'], $obstacles, true)) {
                $position = $next;
            }
        }

        $completed = $position === $config['goal'];
        $commandPenalty = max(0, count($expanded) - $config['minimum_commands']) * 30;
        $timePenalty = min(250, (int) floor($durationMs / 1000) * 2);
        $score = $completed ? max(300, 1000 - $commandPenalty - $timePenalty) : 0;

        return [
            'completed' => $completed,
            'score' => $score,
            'final' => [...$position, 'direction' => $direction],
            'minimum_commands' => $config['minimum_commands'],
        ];
    }

    public function totalItems(string $slug): int
    {
        return count($this->config($slug)['items'] ?? []);
    }

    /** @return array<string, mixed> */
    private function config(string $slug): array
    {
        return match ($slug) {
            'code-runner' => [
                'board_size' => 5,
                'start' => ['x' => 0, 'y' => 4],
                'goal' => ['x' => 4, 'y' => 0],
                'direction' => 'east',
                'obstacles' => [[1, 2], [2, 2], [3, 1]],
                'minimum_commands' => 9,
                'max_commands' => 20,
            ],
            'guardiao-digital' => ['items' => [
                ['id' => 'phishing', 'prompt' => 'Um e-mail urgente pede sua senha para evitar o bloqueio da conta. O que fazer?', 'choices' => [['label' => 'Clicar e informar a senha', 'value' => 'click'], ['label' => 'Abrir o site oficial digitando o endereço', 'value' => 'official'], ['label' => 'Responder pedindo confirmação', 'value' => 'reply']], 'answer' => 'official', 'points' => 200, 'explanation' => 'Mensagens urgentes podem ser phishing. Acesse sempre o serviço pelo endereço oficial e nunca envie sua senha.'],
                ['id' => 'password', 'prompt' => 'Qual senha é mais segura?', 'choices' => [['label' => '12345678', 'value' => 'simple'], ['label' => 'meunome2026', 'value' => 'personal'], ['label' => 'Cacto!Voa_92_Lua', 'value' => 'strong']], 'answer' => 'strong', 'points' => 200, 'explanation' => 'Senhas longas, únicas e difíceis de adivinhar são mais resistentes. Um gerenciador de senhas ajuda muito.'],
                ['id' => 'mfa', 'prompt' => 'Para que serve a autenticação em dois fatores?', 'choices' => [['label' => 'Substituir a senha por um apelido', 'value' => 'nickname'], ['label' => 'Adicionar uma segunda prova de identidade', 'value' => 'second'], ['label' => 'Deixar o login mais rápido', 'value' => 'faster']], 'answer' => 'second', 'points' => 200, 'explanation' => 'O segundo fator reduz o risco mesmo quando a senha é descoberta por outra pessoa.'],
                ['id' => 'link', 'prompt' => 'Você recebeu um link encurtado de um desconhecido. Qual a atitude segura?', 'choices' => [['label' => 'Abrir para descobrir', 'value' => 'open'], ['label' => 'Encaminhar aos amigos', 'value' => 'share'], ['label' => 'Não abrir e verificar a origem', 'value' => 'verify']], 'answer' => 'verify', 'points' => 200, 'explanation' => 'Links desconhecidos podem levar a páginas falsas ou arquivos maliciosos. Confirme a origem antes de abrir.'],
                ['id' => 'privacy', 'prompt' => 'Um quiz pede data de nascimento completa, endereço e documentos sem explicar o motivo. O que fazer?', 'choices' => [['label' => 'Preencher tudo', 'value' => 'fill'], ['label' => 'Evitar e compartilhar apenas o necessário', 'value' => 'minimize'], ['label' => 'Usar os dados de outra pessoa', 'value' => 'fake']], 'answer' => 'minimize', 'points' => 200, 'explanation' => 'Compartilhe apenas os dados necessários e entenda como serão usados. Menos exposição significa menos risco.'],
            ]],
            'rede-em-acao' => ['items' => [
                ['id' => 'route', 'type' => 'sequence', 'prompt' => 'Ordene o caminho de uma requisição até chegar ao servidor e voltar.', 'nodes' => [['label' => 'Celular', 'value' => 'phone'], ['label' => 'Wi-Fi', 'value' => 'wifi'], ['label' => 'Roteador', 'value' => 'router'], ['label' => 'Internet', 'value' => 'internet'], ['label' => 'Servidor', 'value' => 'server'], ['label' => 'Resposta', 'value' => 'response']], 'answer' => ['phone', 'wifi', 'router', 'internet', 'server', 'response'], 'points' => 400, 'explanation' => 'O cliente envia a requisição pela rede local e pelo roteador; a Internet leva os dados ao servidor, que processa e devolve a resposta.'],
                ['id' => 'server', 'type' => 'choice', 'prompt' => 'O que o servidor faz ao receber uma requisição HTTP?', 'choices' => [['label' => 'Processa a solicitação e produz uma resposta', 'value' => 'process'], ['label' => 'Desliga o roteador', 'value' => 'shutdown'], ['label' => 'Troca a senha do Wi-Fi', 'value' => 'password']], 'answer' => 'process', 'points' => 300, 'explanation' => 'O servidor interpreta a requisição, executa a lógica necessária e devolve uma resposta ao cliente.'],
                ['id' => 'latency', 'type' => 'choice', 'prompt' => 'O que significa latência?', 'choices' => [['label' => 'A cor do cabo de rede', 'value' => 'color'], ['label' => 'O tempo que os dados levam para ir e voltar', 'value' => 'time'], ['label' => 'A quantidade de senhas', 'value' => 'passwords']], 'answer' => 'time', 'points' => 300, 'explanation' => 'Latência é o atraso entre enviar uma solicitação e receber a resposta. Distância e qualidade da rede influenciam esse tempo.'],
            ]],
            default => throw new InvalidArgumentException('Jogo não suportado.'),
        };
    }
}

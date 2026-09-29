<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useToast } from 'primevue/usetoast';
import { computed, ref } from 'vue';

const props = defineProps({
    game: { type: Object, required: true },
    config: { type: Object, required: true },
});
const toast = useToast();
const commands = ref([]);
const robot = ref({ ...props.config.start, direction: props.config.direction });
const session = ref(null);
const executing = ref(false);
const result = ref(null);
const startedAt = ref(null);
const commandOptions = [
    { value: 'up', label: 'Cima', icon: 'pi pi-arrow-up' },
    { value: 'down', label: 'Baixo', icon: 'pi pi-arrow-down' },
    { value: 'left', label: 'Esquerda', icon: 'pi pi-arrow-left' },
    { value: 'right', label: 'Direita', icon: 'pi pi-arrow-right' },
];
const movements = {
    up: { x: 0, y: -1, direction: 'north' },
    down: { x: 0, y: 1, direction: 'south' },
    left: { x: -1, y: 0, direction: 'west' },
    right: { x: 1, y: 0, direction: 'east' },
};
const directionIcon = computed(
    () =>
        ({ north: '↑', east: '→', south: '↓', west: '←' })[
            robot.value.direction
        ],
);

const startSession = async () => {
    if (session.value) return;
    const { data } = await axios.post(
        route('game-sessions.store', props.game.slug),
    );
    session.value = data.session;
    startedAt.value = Date.now();
    await axios.post(
        route('game-sessions.events', [props.game.slug, session.value.id]),
        {
            events: [
                { type: 'game_opened', level: 1, payload: {} },
                { type: 'level_started', level: 1, payload: {} },
            ],
        },
    );
};

const addCommand = (command) => {
    if (!executing.value && commands.value.length < props.config.max_commands)
        commands.value.push(command);
};

const step = (command) => {
    const movement = movements[command];
    if (!movement) return;

    robot.value.direction = movement.direction;
    const next = {
        ...robot.value,
        x: robot.value.x + movement.x,
        y: robot.value.y + movement.y,
    };
    const blocked = props.config.obstacles.some(
        ([x, y]) => x === next.x && y === next.y,
    );
    const inside =
        next.x >= 0 &&
        next.x < props.config.board_size &&
        next.y >= 0 &&
        next.y < props.config.board_size;
    if (inside && !blocked) robot.value = next;
};

const execute = async () => {
    if (!commands.value.length || executing.value) return;
    executing.value = true;
    result.value = null;
    robot.value = { ...props.config.start, direction: props.config.direction };
    try {
        await startSession();
        for (const command of commands.value) {
            step(command);
            await new Promise((resolve) => window.setTimeout(resolve, 330));
        }
        const duration = Math.max(1000, Date.now() - startedAt.value);
        const { data } = await axios.post(
            route('game-sessions.complete', [
                props.game.slug,
                session.value.id,
            ]),
            { duration_ms: duration, payload: { commands: commands.value } },
        );
        result.value = data;
        session.value = null;
        startedAt.value = null;
        toast.add({
            severity: data.completed ? 'success' : 'warn',
            summary: data.completed ? 'Algoritmo concluído!' : 'Quase lá!',
            detail: data.completed
                ? `Você marcou ${data.score} pontos.`
                : 'Ajuste a sequência e tente novamente.',
            life: 5000,
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Não foi possível executar',
            detail: error.response?.data?.message ?? 'Tente novamente.',
            life: 5000,
        });
    } finally {
        executing.value = false;
    }
};

const reset = () => {
    if (executing.value) return;
    commands.value = [];
    robot.value = { ...props.config.start, direction: props.config.direction };
    session.value = null;
    startedAt.value = null;
    result.value = null;
};
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-[1fr_.72fr]">
        <section class="glass-card rounded-3xl p-5 sm:p-7">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-campus-cyan text-sm font-bold">MISSÃO</p>
                    <p class="text-slate-300">
                        Leve o robô até a estrela sem bater nos blocos.
                    </p>
                </div>
                <span class="rounded-full bg-white/7 px-3 py-1 text-xs"
                    >mínimo: {{ config.minimum_commands }} comandos</span
                >
            </div>
            <div
                class="bg-campus-deep mx-auto grid aspect-square w-full max-w-xl gap-1 rounded-2xl p-2"
                :style="{
                    gridTemplateColumns: `repeat(${config.board_size}, 1fr)`,
                }"
            >
                <div
                    v-for="index in config.board_size * config.board_size"
                    :key="index"
                    class="bg-campus-navy/80 relative flex items-center justify-center rounded-lg border border-cyan-300/8"
                >
                    <span
                        v-if="
                            config.obstacles.some(
                                ([x, y]) =>
                                    x === (index - 1) % config.board_size &&
                                    y ===
                                        Math.floor(
                                            (index - 1) / config.board_size,
                                        ),
                            )
                        "
                        class="h-3/5 w-3/5 rounded-lg bg-slate-600 shadow-inner"
                    />
                    <i
                        v-if="
                            config.goal.x === (index - 1) % config.board_size &&
                            config.goal.y ===
                                Math.floor((index - 1) / config.board_size)
                        "
                        class="pi pi-star-fill text-campus-gold text-2xl sm:text-4xl"
                    />
                    <span
                        v-if="
                            robot.x === (index - 1) % config.board_size &&
                            robot.y ===
                                Math.floor((index - 1) / config.board_size)
                        "
                        class="bg-campus-blue absolute z-10 flex h-4/5 w-4/5 items-center justify-center rounded-xl text-xl font-black shadow-[0_0_24px_rgba(22,185,255,.55)] transition-all sm:text-3xl"
                        >{{ directionIcon }}</span
                    >
                </div>
            </div>
        </section>

        <section class="glass-card rounded-3xl p-5 sm:p-7">
            <p class="text-campus-gold text-sm font-bold">SEU ALGORITMO</p>
            <h2 class="mt-1 text-2xl font-black">Monte a sequência</h2>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <Button
                    v-for="option in commandOptions"
                    :key="option.value"
                    :label="option.label"
                    :icon="option.icon"
                    severity="secondary"
                    outlined
                    :disabled="
                        executing || commands.length >= config.max_commands
                    "
                    @click="addCommand(option.value)"
                />
            </div>
            <div
                class="mt-5 min-h-30 rounded-2xl border border-dashed border-cyan-200/20 bg-black/15 p-4"
            >
                <p
                    v-if="!commands.length"
                    class="py-8 text-center text-sm text-slate-500"
                >
                    Toque nos comandos para começar
                </p>
                <div v-else class="flex flex-wrap gap-2">
                    <button
                        v-for="(command, index) in commands"
                        :key="`${command}-${index}`"
                        type="button"
                        class="bg-campus-blue/18 text-campus-cyan rounded-lg px-3 py-2 text-sm font-bold"
                        :disabled="executing"
                        @click="commands.splice(index, 1)"
                    >
                        {{ index + 1 }}.
                        {{
                            commandOptions.find(
                                (item) => item.value === command,
                            )?.label
                        }}
                        <i class="pi pi-times ml-1 text-xs" />
                    </button>
                </div>
            </div>
            <Message
                v-if="result"
                :severity="result.completed ? 'success' : 'warn'"
                class="mt-4"
                >{{
                    result.completed
                        ? `Objetivo alcançado: ${result.score} pontos!`
                        : 'O robô ainda não chegou à estrela.'
                }}</Message
            >
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <Button
                    label="EXECUTAR"
                    icon="pi pi-play"
                    size="large"
                    :loading="executing"
                    :disabled="!commands.length"
                    @click="execute"
                /><Button
                    label="LIMPAR"
                    icon="pi pi-refresh"
                    severity="secondary"
                    outlined
                    :disabled="executing"
                    @click="reset"
                />
            </div>
            <div
                class="bg-campus-cyan/7 mt-6 rounded-2xl p-4 text-sm text-slate-300"
            >
                <strong class="text-campus-cyan"
                    >O que você está aprendendo:</strong
                >
                algoritmo é uma sequência ordenada de instruções. Cada comando
                move o robô uma casa na direção indicada.
            </div>
        </section>
    </div>
</template>

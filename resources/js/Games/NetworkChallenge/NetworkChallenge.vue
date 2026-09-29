<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import Message from 'primevue/message';
import ProgressBar from 'primevue/progressbar';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    game: { type: Object, required: true },
    config: { type: Object, required: true },
});
const toast = useToast();
const session = ref(null);
const currentIndex = ref(0);
const sequence = ref([]);
const feedback = ref(null);
const sending = ref(false);
const completed = ref(null);
const startedAt = ref(Date.now());
const itemStartedAt = ref(Date.now());
const packetStep = ref(0);
const current = computed(() => props.config.items[currentIndex.value]);
const progress = computed(() =>
    Math.round((currentIndex.value / props.config.total) * 100),
);

watch(
    current,
    (item) => {
        sequence.value = item?.nodes
            ? [...item.nodes].sort(() => Math.random() - 0.5)
            : [];
    },
    { immediate: true },
);

onMounted(async () => {
    try {
        const { data } = await axios.post(
            route('game-sessions.store', props.game.slug),
        );
        session.value = data.session;
        startedAt.value = Date.now();
        itemStartedAt.value = Date.now();
        await axios.post(
            route('game-sessions.events', [props.game.slug, session.value.id]),
            {
                events: [
                    { type: 'game_opened', level: 1, payload: {} },
                    { type: 'level_started', level: 1, payload: {} },
                ],
            },
        );
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Falha ao iniciar',
            detail: error.response?.data?.message ?? 'Recarregue a página.',
            life: 5000,
        });
    }
});

const move = (index, offset) => {
    const target = index + offset;
    if (target < 0 || target >= sequence.value.length) return;
    [sequence.value[index], sequence.value[target]] = [
        sequence.value[target],
        sequence.value[index],
    ];
};

const submit = async (answer) => {
    if (feedback.value || sending.value) return;
    sending.value = true;
    try {
        const { data } = await axios.post(
            route('game-sessions.answer', [props.game.slug, session.value.id]),
            {
                item_id: current.value.id,
                answer,
                response_ms: Date.now() - itemStartedAt.value,
            },
        );
        feedback.value = data;
        if (current.value.type === 'sequence') animatePacket();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Não foi possível validar',
            detail: error.response?.data?.message ?? 'Tente novamente.',
            life: 4500,
        });
    } finally {
        sending.value = false;
    }
};

const animatePacket = () => {
    packetStep.value = 0;
    const timer = window.setInterval(() => {
        packetStep.value += 1;
        if (packetStep.value >= sequence.value.length - 1)
            window.clearInterval(timer);
    }, 350);
};

const next = async () => {
    if (currentIndex.value < props.config.total - 1) {
        currentIndex.value += 1;
        feedback.value = null;
        itemStartedAt.value = Date.now();
        return;
    }
    sending.value = true;
    try {
        const { data } = await axios.post(
            route('game-sessions.complete', [
                props.game.slug,
                session.value.id,
            ]),
            {
                duration_ms: Math.max(1000, Date.now() - startedAt.value),
                payload: {},
            },
        );
        completed.value = data;
        toast.add({
            severity: 'success',
            summary: 'Rede conectada!',
            detail: `${data.score} pontos na missão.`,
            life: 5000,
        });
    } finally {
        sending.value = false;
    }
};
</script>

<template>
    <div class="mx-auto max-w-5xl">
        <section v-if="!completed" class="glass-card rounded-3xl p-5 sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-campus-cyan text-sm font-black">
                        PACOTE {{ currentIndex + 1 }} DE {{ config.total }}
                    </p>
                    <h2 class="mt-1 text-2xl font-black sm:text-3xl">
                        Faça a mensagem chegar
                    </h2>
                </div>
                <span
                    class="bg-campus-blue/20 text-campus-cyan flex h-14 w-14 items-center justify-center rounded-2xl text-2xl"
                    ><i class="pi pi-globe"
                /></span>
            </div>
            <ProgressBar
                :value="progress"
                :show-value="false"
                class="mt-6 h-2"
            />
            <p class="mt-7 text-lg font-bold">{{ current.prompt }}</p>

            <div v-if="current.type === 'sequence'" class="mt-6">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(node, index) in sequence"
                        :key="node.value"
                        class="relative rounded-2xl border p-4 transition"
                        :class="
                            packetStep === index
                                ? 'border-campus-gold bg-campus-gold/12 shadow-[0_0_25px_rgba(255,191,0,.2)]'
                                : 'border-cyan-200/15 bg-white/5'
                        "
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-bold"
                                ><span class="text-campus-cyan mr-2">{{
                                    index + 1
                                }}</span
                                >{{ node.label }}</span
                            >
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    class="focus-ring rounded-lg bg-white/8 p-2"
                                    :disabled="index === 0 || feedback"
                                    aria-label="Mover para cima"
                                    @click="move(index, -1)"
                                >
                                    <i class="pi pi-arrow-up" /></button
                                ><button
                                    type="button"
                                    class="focus-ring rounded-lg bg-white/8 p-2"
                                    :disabled="
                                        index === sequence.length - 1 ||
                                        feedback
                                    "
                                    aria-label="Mover para baixo"
                                    @click="move(index, 1)"
                                >
                                    <i class="pi pi-arrow-down" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <Button
                    label="ENVIAR PACOTE"
                    icon="pi pi-send"
                    class="mt-5 w-full sm:w-auto"
                    :loading="sending"
                    :disabled="!!feedback || !session"
                    @click="submit(sequence.map((node) => node.value))"
                />
            </div>
            <div v-else class="mt-5 grid gap-3">
                <button
                    v-for="choice in current.choices"
                    :key="choice.value"
                    type="button"
                    :disabled="feedback || sending || !session"
                    class="focus-ring rounded-2xl border border-cyan-200/15 bg-white/5 p-4 text-left font-semibold"
                    @click="submit(choice.value)"
                >
                    {{ choice.label }}
                </button>
            </div>

            <Message
                v-if="feedback"
                :severity="feedback.correct ? 'success' : 'warn'"
                class="mt-5"
                ><strong>{{
                    feedback.correct
                        ? 'Conexão correta!'
                        : 'O pacote se perdeu.'
                }}</strong>
                {{ feedback.explanation }}</Message
            >
            <Button
                v-if="feedback"
                :label="
                    currentIndex === config.total - 1
                        ? 'VER RESULTADO'
                        : 'PRÓXIMO PACOTE'
                "
                icon="pi pi-arrow-right"
                icon-pos="right"
                class="mt-5"
                :loading="sending"
                @click="next"
            />
            <div
                class="bg-campus-cyan/7 mt-7 rounded-2xl p-4 text-sm text-slate-300"
            >
                <strong class="text-campus-cyan">Na Internet:</strong> cliente,
                roteador e servidor cooperam. A requisição viaja por diferentes
                redes, é processada e volta como resposta.
            </div>
        </section>
        <section v-else class="glass-card rounded-3xl p-8 text-center">
            <span
                class="bg-campus-blue/20 text-campus-cyan mx-auto flex h-20 w-20 items-center justify-center rounded-full text-4xl"
                ><i class="pi pi-wifi"
            /></span>
            <p class="text-campus-gold mt-6 text-sm font-black tracking-widest">
                REDE EM AÇÃO
            </p>
            <h2 class="mt-2 text-4xl font-black">Servidor respondeu!</h2>
            <p class="text-campus-cyan mt-4 text-6xl font-black">
                {{ completed.score }}
            </p>
            <p class="text-slate-400">pontos</p>
            <div class="mt-7 flex justify-center gap-3">
                <a :href="route('dashboard')"
                    ><Button label="VOLTAR AO PAINEL" /></a
                ><a :href="route('rankings.index')"
                    ><Button label="RANKING" severity="secondary" outlined
                /></a>
            </div>
        </section>
    </div>
</template>

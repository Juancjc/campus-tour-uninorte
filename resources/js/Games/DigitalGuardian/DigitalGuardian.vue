<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import Message from 'primevue/message';
import ProgressBar from 'primevue/progressbar';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    game: { type: Object, required: true },
    config: { type: Object, required: true },
});
const toast = useToast();
const session = ref(null);
const currentIndex = ref(0);
const feedback = ref(null);
const sending = ref(false);
const completed = ref(null);
const startedAt = ref(Date.now());
const questionStartedAt = ref(Date.now());
const current = computed(() => props.config.items[currentIndex.value]);
const progress = computed(() =>
    Math.round((currentIndex.value / props.config.total) * 100),
);

onMounted(async () => {
    try {
        const { data } = await axios.post(
            route('game-sessions.store', props.game.slug),
        );
        session.value = data.session;
        startedAt.value = Date.now();
        questionStartedAt.value = Date.now();
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

const answer = async (value) => {
    if (feedback.value || sending.value) return;
    sending.value = true;
    try {
        const { data } = await axios.post(
            route('game-sessions.answer', [props.game.slug, session.value.id]),
            {
                item_id: current.value.id,
                answer: value,
                response_ms: Date.now() - questionStartedAt.value,
            },
        );
        feedback.value = data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Resposta não enviada',
            detail: error.response?.data?.message ?? 'Tente novamente.',
            life: 4500,
        });
    } finally {
        sending.value = false;
    }
};

const next = async () => {
    if (currentIndex.value < props.config.total - 1) {
        currentIndex.value += 1;
        feedback.value = null;
        questionStartedAt.value = Date.now();
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
            summary: 'Missão concluída!',
            detail: `${data.score} pontos no Guardião Digital.`,
            life: 5000,
        });
    } finally {
        sending.value = false;
    }
};
</script>

<template>
    <div class="mx-auto max-w-4xl">
        <section v-if="!completed" class="glass-card rounded-3xl p-5 sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-campus-cyan text-sm font-black">
                        SITUAÇÃO {{ currentIndex + 1 }} DE {{ config.total }}
                    </p>
                    <h2 class="mt-1 text-2xl font-black sm:text-3xl">
                        Decida rápido. Navegue seguro.
                    </h2>
                </div>
                <span
                    class="bg-campus-blue/20 text-campus-cyan flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-2xl"
                    ><i class="pi pi-shield"
                /></span>
            </div>
            <ProgressBar
                :value="progress"
                :show-value="false"
                class="mt-6 h-2"
            />
            <div
                class="mt-7 rounded-2xl border border-white/10 bg-black/18 p-5 sm:p-7"
            >
                <p class="text-lg leading-relaxed font-bold sm:text-xl">
                    {{ current.prompt }}
                </p>
            </div>
            <div class="mt-5 grid gap-3">
                <button
                    v-for="choice in current.choices"
                    :key="choice.value"
                    type="button"
                    :disabled="feedback || sending || !session"
                    class="focus-ring hover:border-campus-cyan/50 rounded-2xl border border-cyan-200/15 bg-white/5 p-4 text-left font-semibold transition disabled:opacity-60"
                    @click="answer(choice.value)"
                >
                    <span
                        class="bg-campus-blue/20 text-campus-cyan mr-3 inline-flex h-8 w-8 items-center justify-center rounded-lg"
                        ><i class="pi pi-angle-right" /></span
                    >{{ choice.label }}
                </button>
            </div>
            <Message
                v-if="feedback"
                :severity="feedback.correct ? 'success' : 'warn'"
                class="mt-5"
                ><strong>{{
                    feedback.correct ? 'Boa escolha!' : 'Atenção!'
                }}</strong>
                {{ feedback.explanation }}</Message
            >
            <Button
                v-if="feedback"
                :label="
                    currentIndex === config.total - 1
                        ? 'VER MEU RESULTADO'
                        : 'PRÓXIMA SITUAÇÃO'
                "
                icon="pi pi-arrow-right"
                icon-pos="right"
                class="mt-5 w-full sm:w-auto"
                :loading="sending"
                @click="next"
            />
        </section>
        <section v-else class="glass-card rounded-3xl p-8 text-center">
            <span
                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-400/15 text-4xl text-emerald-300"
                ><i class="pi pi-shield"
            /></span>
            <p class="text-campus-gold mt-6 text-sm font-black tracking-widest">
                GUARDIÃO DIGITAL
            </p>
            <h2 class="mt-2 text-4xl font-black">Missão concluída!</h2>
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

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import ProgressBar from 'primevue/progressbar';

defineProps({
    games: { type: Array, required: true },
    progress: { type: Object, required: true },
    stats: { type: Object, required: true },
    achievements: { type: Array, required: true },
    campus_tour_completed: { type: Boolean, required: true },
});
const page = usePage();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Meu Campus Tour" />
        <section
            v-if="campus_tour_completed"
            class="border-campus-gold/35 from-campus-orange/25 via-campus-gold/10 to-campus-blue/20 mb-8 overflow-hidden rounded-3xl border bg-gradient-to-r p-7 sm:p-9"
        >
            <span class="text-campus-gold text-sm font-black tracking-[.2em]"
                >MISSÃO COMPLETA</span
            >
            <h1 class="mt-2 text-4xl font-black">Campus Tour concluído! 🎉</h1>
            <p class="mt-3 max-w-2xl text-slate-200">
                Você explorou programação, segurança e redes. Veja seu score,
                suas conquistas e continue subindo no ranking.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="/#cursos"
                    ><Button
                        label="CONHEÇA SI E ADS"
                        icon="pi pi-graduation-cap" /></a
                ><Link :href="route('rankings.index')"
                    ><Button label="VER RANKING" severity="secondary" outlined
                /></Link>
            </div>
        </section>

        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <p class="text-campus-cyan text-sm font-black tracking-[.2em]">
                    SEU PAINEL
                </p>
                <h1 class="mt-2 text-4xl font-black sm:text-5xl">
                    Olá, {{ page.props.auth.user.name.split(' ')[0] }}!
                </h1>
                <p class="mt-3 text-slate-300">
                    {{
                        progress.completed === 0
                            ? 'Escolha seu primeiro desafio e comece agora.'
                            : 'Continue: cada jogo concluído aumenta sua pontuação.'
                    }}
                </p>
            </div>
            <div class="glass-card min-w-64 rounded-2xl p-5">
                <div class="flex justify-between text-sm">
                    <span>Progresso</span
                    ><strong
                        >{{ progress.completed }}/{{ progress.total }}</strong
                    >
                </div>
                <ProgressBar
                    :value="progress.percentage"
                    :show-value="false"
                    class="mt-3 h-2"
                />
                <p class="text-campus-cyan mt-2 text-right text-xs">
                    {{ progress.percentage }}% concluído
                </p>
            </div>
        </div>

        <section class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="glass-card rounded-2xl p-5">
                <i class="pi pi-bolt text-campus-gold" />
                <p class="mt-3 text-3xl font-black">{{ stats.points }}</p>
                <p class="text-sm text-slate-400">pontos</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <i class="pi pi-trophy text-campus-cyan" />
                <p class="mt-3 text-3xl font-black">#{{ stats.position }}</p>
                <p class="text-sm text-slate-400">posição geral</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <i class="pi pi-refresh text-campus-cyan" />
                <p class="mt-3 text-3xl font-black">{{ stats.attempts }}</p>
                <p class="text-sm text-slate-400">tentativas</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <i class="pi pi-star text-campus-gold" />
                <p class="mt-3 text-3xl font-black">
                    {{ achievements.length }}
                </p>
                <p class="text-sm text-slate-400">conquistas</p>
            </div>
        </section>

        <section class="mt-12">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <p
                        class="text-campus-gold text-xs font-black tracking-widest"
                    >
                        DESAFIOS
                    </p>
                    <h2 class="mt-1 text-3xl font-black">
                        Escolha seu próximo jogo
                    </h2>
                </div>
                <Link
                    :href="route('rankings.index')"
                    class="text-campus-cyan text-sm font-bold"
                    >Ranking <i class="pi pi-arrow-right"
                /></Link>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <article
                    v-for="game in games"
                    :key="game.slug"
                    class="glass-card flex flex-col rounded-3xl p-6"
                >
                    <div class="flex items-start justify-between">
                        <span
                            class="bg-campus-blue/16 text-campus-cyan flex h-14 w-14 items-center justify-center rounded-2xl text-2xl"
                            ><i :class="game.icon" /></span
                        ><span
                            class="rounded-full px-3 py-1 text-xs font-bold"
                            :class="
                                game.completed
                                    ? 'bg-emerald-400/15 text-emerald-200'
                                    : 'bg-white/7 text-slate-300'
                            "
                            >{{ game.completed ? 'CONCLUÍDO' : 'NOVO' }}</span
                        >
                    </div>
                    <p
                        class="text-campus-gold mt-5 text-xs font-bold tracking-widest uppercase"
                    >
                        {{ game.category }}
                    </p>
                    <h3 class="mt-1 text-2xl font-black">{{ game.name }}</h3>
                    <p class="mt-3 grow text-sm leading-relaxed text-slate-300">
                        {{ game.description }}
                    </p>
                    <div class="mt-5 flex items-center justify-between text-sm">
                        <span
                            >Recorde:
                            <strong>{{ game.best_score }}</strong></span
                        ><span>{{ game.attempts }} tentativas</span>
                    </div>
                    <Link :href="route('games.show', game.slug)" class="mt-5"
                        ><Button
                            :label="
                                game.completed
                                    ? 'JOGAR NOVAMENTE'
                                    : 'JOGAR AGORA'
                            "
                            icon="pi pi-play"
                            class="w-full"
                    /></Link>
                </article>
            </div>
        </section>

        <section v-if="achievements.length" class="mt-12">
            <h2 class="text-3xl font-black">Suas conquistas</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="achievement in achievements"
                    :key="achievement.slug"
                    class="border-campus-gold/15 bg-campus-gold/5 flex items-center gap-4 rounded-2xl border p-4"
                >
                    <span
                        class="bg-campus-gold/15 text-campus-gold flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                        ><i :class="achievement.icon"
                    /></span>
                    <div>
                        <p class="font-bold">{{ achievement.name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ achievement.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

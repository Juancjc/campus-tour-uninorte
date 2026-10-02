<script setup>
import CodeRunner from '@/Games/CodeRunner/CodeRunner.vue';
import DigitalGuardian from '@/Games/DigitalGuardian/DigitalGuardian.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    game: { type: Object, required: true },
    config: { type: Object, required: true },
    bestScore: { type: Number, default: 0 },
});
const components = {
    'code-runner': CodeRunner,
    'guardiao-digital': DigitalGuardian,
};
const gameComponent = computed(() => components[props.game.slug]);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="game.name" />
        <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
            <div>
                <Link
                    :href="route('dashboard')"
                    class="text-campus-cyan text-sm"
                    ><i class="pi pi-arrow-left mr-2" />Voltar ao painel</Link
                >
                <p
                    class="text-campus-gold mt-4 text-xs font-black tracking-[.2em] uppercase"
                >
                    {{ game.category }}
                </p>
                <h1 class="mt-1 text-4xl font-black sm:text-5xl">
                    {{ game.name }}
                </h1>
                <p class="mt-2 max-w-2xl text-slate-300">
                    {{ game.description }}
                </p>
            </div>
            <div class="glass-card rounded-2xl p-4 text-center">
                <p class="text-xs tracking-widest text-slate-400 uppercase">
                    Seu recorde
                </p>
                <p class="text-campus-gold mt-1 text-3xl font-black">
                    {{ bestScore }}
                </p>
            </div>
        </div>
        <component :is="gameComponent" :game="game" :config="config" />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

defineProps({
    rankings: { type: Object, required: true },
    overall: { type: Object, required: true },
});
const formatTime = (milliseconds) =>
    milliseconds ? `${(milliseconds / 1000).toFixed(1)}s` : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Rankings" />
        <div class="text-center">
            <p class="text-campus-gold text-sm font-black tracking-[.2em]">
                PLACAR AO VIVO
            </p>
            <h1 class="mt-2 text-4xl font-black sm:text-5xl">
                Quem está no topo?
            </h1>
            <p class="mt-3 text-slate-300">
                Maior pontuação vence. Em caso de empate, vale o menor tempo e
                quem chegou primeiro.
            </p>
        </div>
        <Tabs value="overall" class="mt-8">
            <TabList
                ><Tab value="overall">Geral</Tab
                ><Tab
                    v-for="entry in rankings"
                    :key="entry.game.slug"
                    :value="entry.game.slug"
                    >{{ entry.game.name }}</Tab
                ></TabList
            >
            <TabPanels>
                <TabPanel value="overall"
                    ><div class="glass-card overflow-hidden rounded-3xl">
                        <table class="w-full text-left">
                            <thead
                                class="bg-white/6 text-xs tracking-widest text-slate-400 uppercase"
                            >
                                <tr>
                                    <th class="p-4">#</th>
                                    <th class="p-4">Participante</th>
                                    <th class="hidden p-4 sm:table-cell">
                                        Instituição
                                    </th>
                                    <th class="p-4 text-right">Pontos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in overall.top"
                                    :key="row.user_id"
                                    class="border-t border-white/7"
                                >
                                    <td
                                        class="p-4 text-xl font-black"
                                        :class="
                                            row.position <= 3
                                                ? 'text-campus-gold'
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{ row.position }}
                                    </td>
                                    <td class="p-4 font-bold">
                                        {{ row.name }}
                                    </td>
                                    <td
                                        class="hidden p-4 text-slate-400 sm:table-cell"
                                    >
                                        {{ row.school_name || '—' }}
                                    </td>
                                    <td
                                        class="text-campus-cyan p-4 text-right text-xl font-black"
                                    >
                                        {{ row.total_score }}
                                    </td>
                                </tr>
                                <tr v-if="!overall.top.length">
                                    <td
                                        colspan="4"
                                        class="p-10 text-center text-slate-400"
                                    >
                                        O ranking começa com você.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-if="overall.me && overall.me.position > 10"
                        class="border-campus-cyan/25 bg-campus-cyan/7 mt-4 rounded-2xl border p-4"
                    >
                        <p class="text-sm text-slate-300">
                            Sua posição:
                            <strong class="text-campus-cyan ml-2 text-xl"
                                >#{{ overall.me.position }}</strong
                            >
                            • {{ overall.me.total_score }} pontos
                        </p>
                    </div></TabPanel
                >
                <TabPanel
                    v-for="entry in rankings"
                    :key="entry.game.slug"
                    :value="entry.game.slug"
                    ><div class="glass-card overflow-hidden rounded-3xl">
                        <table class="w-full text-left">
                            <thead
                                class="bg-white/6 text-xs tracking-widest text-slate-400 uppercase"
                            >
                                <tr>
                                    <th class="p-4">#</th>
                                    <th class="p-4">Participante</th>
                                    <th class="p-4 text-right">Pontos</th>
                                    <th
                                        class="hidden p-4 text-right sm:table-cell"
                                    >
                                        Tempo
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in entry.top"
                                    :key="row.user_id"
                                    class="border-t border-white/7"
                                >
                                    <td
                                        class="p-4 text-xl font-black"
                                        :class="
                                            row.position <= 3
                                                ? 'text-campus-gold'
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{ row.position }}
                                    </td>
                                    <td class="p-4 font-bold">
                                        {{ row.name }}
                                    </td>
                                    <td
                                        class="text-campus-cyan p-4 text-right text-xl font-black"
                                    >
                                        {{ row.best_score }}
                                    </td>
                                    <td
                                        class="hidden p-4 text-right text-slate-400 sm:table-cell"
                                    >
                                        {{ formatTime(row.best_duration_ms) }}
                                    </td>
                                </tr>
                                <tr v-if="!entry.top.length">
                                    <td
                                        colspan="4"
                                        class="p-10 text-center text-slate-400"
                                    >
                                        Ninguém concluiu este desafio ainda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-if="entry.me && entry.me.position > 10"
                        class="border-campus-cyan/25 bg-campus-cyan/7 mt-4 rounded-2xl border p-4"
                    >
                        Sua posição:
                        <strong class="text-campus-cyan"
                            >#{{ entry.me.position }}</strong
                        >
                        • {{ entry.me.best_score }} pontos
                    </div></TabPanel
                >
            </TabPanels>
        </Tabs>
    </AuthenticatedLayout>
</template>

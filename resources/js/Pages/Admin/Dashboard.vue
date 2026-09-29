<script setup>
import MetricBar from '@/Components/MetricBar.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { reactive } from 'vue';

const props = defineProps({
    metrics: { type: Object, required: true },
    accesses_by_hour: { type: Array, required: true },
    games: { type: Array, required: true },
    devices: { type: Array, required: true },
    browsers: { type: Array, required: true },
    systems: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});
const form = reactive({
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});
const filter = () =>
    router.get(route('admin.dashboard'), form, {
        preserveState: true,
        replace: true,
    });
const cards = [
    ['users', 'Usuários', 'pi pi-users'],
    ['visitors', 'Visitantes', 'pi pi-eye'],
    ['accesses', 'Acessos', 'pi pi-chart-line'],
    ['players', 'Jogadores', 'pi pi-play'],
    ['sessions_started', 'Partidas', 'pi pi-flag'],
    ['sessions_completed', 'Conclusões', 'pi pi-check-circle'],
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Administração" />
        <div class="flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="text-campus-gold text-sm font-black tracking-[.2em]">
                    ADMINISTRAÇÃO
                </p>
                <h1 class="mt-2 text-4xl font-black">Visão geral do evento</h1>
            </div>
            <Link :href="route('admin.reports.index')"
                ><Button label="RELATÓRIOS" icon="pi pi-file-export"
            /></Link>
        </div>
        <form
            class="glass-card mt-7 flex flex-wrap items-end gap-3 rounded-2xl p-4"
            @submit.prevent="filter"
        >
            <label class="grid gap-1 text-xs text-slate-400"
                >De<InputText v-model="form.from" type="date" /></label
            ><label class="grid gap-1 text-xs text-slate-400"
                >Até<InputText v-model="form.to" type="date" /></label
            ><Button type="submit" label="FILTRAR" icon="pi pi-filter" />
        </form>
        <section
            class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6"
        >
            <div
                v-for="card in cards"
                :key="card[0]"
                class="glass-card rounded-2xl p-5"
            >
                <i :class="card[2]" class="text-campus-cyan" />
                <p class="mt-3 text-3xl font-black">{{ metrics[card[0]] }}</p>
                <p class="text-sm text-slate-400">{{ card[1] }}</p>
            </div>
        </section>
        <section class="mt-6 grid gap-5 lg:grid-cols-2">
            <div class="glass-card rounded-3xl p-6">
                <h2 class="mb-5 text-xl font-black">Acessos por horário</h2>
                <MetricBar :items="accesses_by_hour" />
            </div>
            <div class="glass-card rounded-3xl p-6">
                <h2 class="mb-5 text-xl font-black">Jogos mais utilizados</h2>
                <MetricBar :items="games" color="gold" />
            </div>
            <div class="glass-card rounded-3xl p-6">
                <h2 class="mb-5 text-xl font-black">Dispositivos</h2>
                <MetricBar :items="devices" />
            </div>
            <div class="glass-card rounded-3xl p-6">
                <h2 class="mb-5 text-xl font-black">Navegadores e sistemas</h2>
                <div class="grid gap-6 sm:grid-cols-2">
                    <MetricBar :items="browsers" /><MetricBar
                        :items="systems"
                        color="gold"
                    />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import { reactive } from 'vue';

const props = defineProps({
    schools: { type: Array, required: true },
    professions: { type: Array, required: true },
    games: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    professionsFilter: { type: Array, required: true },
});
const form = reactive({
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    school: props.filters.school ?? '',
    profession_id: props.filters.profession_id
        ? Number(props.filters.profession_id)
        : null,
});
const filter = () =>
    router.get(route('admin.reports.index'), form, {
        preserveState: true,
        replace: true,
    });
const query = () =>
    new URLSearchParams(
        Object.fromEntries(
            Object.entries(form).filter(
                ([, value]) => value !== '' && value !== null,
            ),
        ),
    ).toString();
const exportUrl = (type, format) =>
    `${route(`admin.reports.${format}`, type)}?${query()}`;
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Relatórios" />
        <div>
            <p class="text-campus-gold text-sm font-black tracking-[.2em]">
                DADOS AGREGADOS
            </p>
            <h1 class="mt-2 text-4xl font-black">Relatórios do Campus Tour</h1>
            <p class="mt-2 text-slate-300">
                Filtre, analise e exporte os resultados sem expor dados
                desnecessários.
            </p>
        </div>
        <form
            class="glass-card mt-7 grid gap-3 rounded-2xl p-4 sm:grid-cols-2 lg:grid-cols-5"
            @submit.prevent="filter"
        >
            <label class="grid gap-1 text-xs text-slate-400"
                >De<InputText v-model="form.from" type="date" /></label
            ><label class="grid gap-1 text-xs text-slate-400"
                >Até<InputText v-model="form.to" type="date" /></label
            ><label class="grid gap-1 text-xs text-slate-400"
                >Instituição<InputText
                    v-model="form.school"
                    placeholder="Buscar escola" /></label
            ><label class="grid gap-1 text-xs text-slate-400"
                >Profissão<Select
                    v-model="form.profession_id"
                    :options="professionsFilter"
                    option-label="name"
                    option-value="id"
                    show-clear
                    placeholder="Todas" /></label
            ><Button
                type="submit"
                label="APLICAR"
                icon="pi pi-filter"
                class="self-end"
            />
        </form>
        <Tabs value="schools" class="mt-7"
            ><TabList
                ><Tab value="schools">Instituições</Tab
                ><Tab value="professions">Profissões</Tab
                ><Tab value="games">Jogos</Tab></TabList
            ><TabPanels>
                <TabPanel value="schools"
                    ><div class="mb-4 flex gap-2">
                        <a :href="exportUrl('schools', 'csv')"
                            ><Button
                                label="CSV"
                                icon="pi pi-download"
                                size="small" /></a
                        ><a :href="exportUrl('schools', 'xlsx')"
                            ><Button
                                label="XLSX"
                                icon="pi pi-file-excel"
                                severity="success"
                                size="small"
                        /></a>
                    </div>
                    <div class="glass-card overflow-x-auto rounded-2xl">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="p-4 text-left">Instituição</th>
                                    <th class="p-4 text-right">Alunos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in schools"
                                    :key="row.name"
                                    class="border-t border-white/7"
                                >
                                    <td class="p-4">{{ row.name }}</td>
                                    <td
                                        class="text-campus-cyan p-4 text-right font-black"
                                    >
                                        {{ row.students }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div></TabPanel
                >
                <TabPanel value="professions"
                    ><div class="mb-4 flex gap-2">
                        <a :href="exportUrl('professions', 'csv')"
                            ><Button
                                label="CSV"
                                icon="pi pi-download"
                                size="small" /></a
                        ><a :href="exportUrl('professions', 'xlsx')"
                            ><Button
                                label="XLSX"
                                icon="pi pi-file-excel"
                                severity="success"
                                size="small"
                        /></a>
                    </div>
                    <div class="glass-card overflow-x-auto rounded-2xl">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="p-4 text-left">Profissão</th>
                                    <th class="p-4 text-left">Categoria</th>
                                    <th class="p-4 text-right">Alunos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in professions"
                                    :key="`${row.name}-${row.category}`"
                                    class="border-t border-white/7"
                                >
                                    <td class="p-4">{{ row.name }}</td>
                                    <td class="p-4 text-slate-400">
                                        {{ row.category }}
                                    </td>
                                    <td
                                        class="text-campus-cyan p-4 text-right font-black"
                                    >
                                        {{ row.students }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div></TabPanel
                >
                <TabPanel value="games"
                    ><div class="mb-4 flex gap-2">
                        <a :href="exportUrl('games', 'csv')"
                            ><Button
                                label="CSV"
                                icon="pi pi-download"
                                size="small" /></a
                        ><a :href="exportUrl('games', 'xlsx')"
                            ><Button
                                label="XLSX"
                                icon="pi pi-file-excel"
                                severity="success"
                                size="small"
                        /></a>
                    </div>
                    <div class="glass-card overflow-x-auto rounded-2xl">
                        <table class="w-full min-w-200">
                            <thead>
                                <tr>
                                    <th
                                        v-for="heading in [
                                            'Jogo',
                                            'Partidas',
                                            'Conclusões',
                                            'Abandonos',
                                            'Média de pontos',
                                            'Tempo médio',
                                        ]"
                                        :key="heading"
                                        class="p-4 text-left"
                                    >
                                        {{ heading }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in games"
                                    :key="row.name"
                                    class="border-t border-white/7"
                                >
                                    <td class="p-4 font-bold">
                                        {{ row.name }}
                                    </td>
                                    <td class="p-4">{{ row.sessions }}</td>
                                    <td class="p-4">{{ row.completions }}</td>
                                    <td class="p-4">{{ row.abandonments }}</td>
                                    <td class="p-4">{{ row.average_score }}</td>
                                    <td class="p-4">
                                        {{
                                            (
                                                row.average_duration_ms / 1000
                                            ).toFixed(1)
                                        }}s
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div></TabPanel
                >
            </TabPanels></Tabs
        >
    </AuthenticatedLayout>
</template>

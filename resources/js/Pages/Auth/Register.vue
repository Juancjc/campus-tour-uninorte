<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import { ref } from 'vue';

const props = defineProps({ professions: { type: Array, required: true } });
const selectedProfession = ref(null);
const suggestions = ref(props.professions);
const form = useForm({
    name: '',
    email: '',
    school_name: '',
    profession_id: null,
    password: '',
    password_confirmation: '',
});

const searchProfession = ({ query }) => {
    const term = query.toLocaleLowerCase('pt-BR');
    suggestions.value = props.professions
        .filter((profession) =>
            `${profession.name} ${profession.category}`
                .toLocaleLowerCase('pt-BR')
                .includes(term),
        )
        .slice(0, 30);
};

const submit = () => {
    form.profession_id = selectedProfession.value?.id ?? null;
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Participar" />
        <div class="mb-6 text-center">
            <span
                class="bg-campus-blue/15 text-campus-cyan rounded-full px-3 py-1 text-xs font-bold tracking-widest uppercase"
                >Entrada rápida</span
            >
            <h1 class="mt-4 text-3xl font-black text-white">
                Entre no Campus Tour
            </h1>
            <p class="mt-2 text-sm text-slate-300">
                Leva menos de um minuto. Depois, escolha seu primeiro desafio.
            </p>
        </div>
        <form class="grid gap-4" @submit.prevent="submit">
            <label class="grid gap-1.5"
                ><span class="text-sm font-semibold">Nome completo</span
                ><InputText
                    v-model="form.name"
                    autocomplete="name"
                    minlength="5"
                    required
                    fluid
                    placeholder="Digite seu nome e sobrenome"
                /><small v-if="form.errors.name" class="text-red-300">{{
                    form.errors.name
                }}</small></label
            >
            <label class="grid gap-1.5"
                ><span class="text-sm font-semibold">E-mail</span
                ><InputText
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                    fluid
                    placeholder="voce@email.com"
                /><small v-if="form.errors.email" class="text-red-300">{{
                    form.errors.email
                }}</small></label
            >
            <label class="grid gap-1.5"
                ><span class="text-sm font-semibold"
                    >Instituição onde estuda</span
                ><InputText
                    v-model="form.school_name"
                    required
                    fluid
                    placeholder="Digite o nome da sua escola"
                /><small v-if="form.errors.school_name" class="text-red-300">{{
                    form.errors.school_name
                }}</small></label
            >
            <label class="grid gap-1.5">
                <span class="text-sm font-semibold"
                    >Profissão ou carreira que imagina</span
                >
                <AutoComplete
                    v-model="selectedProfession"
                    :suggestions="suggestions"
                    option-label="name"
                    dropdown
                    force-selection
                    fluid
                    placeholder="Busque ou escolha uma opção"
                    @complete="searchProfession"
                >
                    <template #option="slotProps"
                        ><div>
                            <strong>{{ slotProps.option.name }}</strong
                            ><small class="ml-2 text-slate-500">{{
                                slotProps.option.category
                            }}</small>
                        </div></template
                    >
                </AutoComplete>
                <small v-if="form.errors.profession_id" class="text-red-300">{{
                    form.errors.profession_id
                }}</small>
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1.5"
                    ><span class="text-sm font-semibold">Senha</span
                    ><Password
                        v-model="form.password"
                        autocomplete="new-password"
                        toggle-mask
                        :feedback="false"
                        fluid
                        required
                    /><small v-if="form.errors.password" class="text-red-300">{{
                        form.errors.password
                    }}</small></label
                >
                <label class="grid gap-1.5"
                    ><span class="text-sm font-semibold">Confirmar senha</span
                    ><Password
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        toggle-mask
                        :feedback="false"
                        fluid
                        required
                /></label>
            </div>
            <Button
                type="submit"
                label="ESCOLHER MEU PRIMEIRO DESAFIO"
                icon="pi pi-arrow-right"
                icon-pos="right"
                size="large"
                :loading="form.processing"
                class="mt-2 w-full"
            />
            <p class="text-center text-sm text-slate-300">
                Já participou?
                <Link :href="route('login')" class="text-campus-cyan font-bold"
                    >Entrar</Link
                >
            </p>
        </form>
    </GuestLayout>
</template>

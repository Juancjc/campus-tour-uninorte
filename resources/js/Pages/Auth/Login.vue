<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

defineProps({ canResetPassword: { type: Boolean }, status: { type: String } });
const form = useForm({ email: '', password: '', remember: false });
const submit = () =>
    form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <GuestLayout>
        <Head title="Entrar" />
        <div class="mb-7 text-center">
            <h1 class="text-3xl font-black">Bem-vindo de volta!</h1>
            <p class="mt-2 text-sm text-slate-300">
                Continue seus desafios e suba no ranking.
            </p>
        </div>
        <p
            v-if="status"
            class="mb-4 rounded-xl bg-emerald-400/10 p-3 text-sm text-emerald-200"
        >
            {{ status }}
        </p>
        <form class="grid gap-5" @submit.prevent="submit">
            <label class="grid gap-1.5"
                ><span class="text-sm font-semibold">E-mail</span
                ><InputText
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    fluid
                    required
                    autofocus
                /><small v-if="form.errors.email" class="text-red-300">{{
                    form.errors.email
                }}</small></label
            >
            <label class="grid gap-1.5"
                ><span class="text-sm font-semibold">Senha</span
                ><Password
                    v-model="form.password"
                    autocomplete="current-password"
                    toggle-mask
                    :feedback="false"
                    fluid
                    required
            /></label>
            <label class="flex items-center gap-2 text-sm text-slate-300"
                ><Checkbox v-model="form.remember" binary input-id="remember" />
                Manter conectado</label
            >
            <Button
                type="submit"
                label="ENTRAR NO CAMPUS TOUR"
                icon="pi pi-sign-in"
                :loading="form.processing"
                size="large"
            />
            <div class="flex flex-wrap justify-between gap-3 text-sm">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-slate-300"
                    >Esqueci minha senha</Link
                ><Link
                    :href="route('register')"
                    class="text-campus-cyan font-bold"
                    >Quero participar</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>

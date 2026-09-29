<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ProjectCredits from '@/Components/ProjectCredits.vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { ref, watch } from 'vue';

const page = usePage();
const toast = useToast();
const menuOpen = ref(false);

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success)
            toast.add({
                severity: 'success',
                summary: 'Tudo certo!',
                detail: flash.success,
                life: 4500,
            });
        if (flash?.error)
            toast.add({
                severity: 'error',
                summary: 'Ops!',
                detail: flash.error,
                life: 5000,
            });
    },
    { deep: true, immediate: true },
);

const links = [
    { label: 'Painel', route: 'dashboard', icon: 'pi pi-home' },
    { label: 'Ranking', route: 'rankings.index', icon: 'pi pi-trophy' },
];
</script>

<template>
    <div class="tech-grid bg-campus-navy min-h-screen text-slate-100">
        <Toast position="top-right" />
        <header
            class="bg-campus-navy/90 sticky top-0 z-40 border-b border-cyan-300/10 backdrop-blur-xl"
        >
            <nav
                class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6"
            >
                <Link
                    :href="route('dashboard')"
                    class="focus-ring rounded-xl py-1"
                    ><ApplicationLogo compact
                /></Link>
                <div class="hidden items-center gap-2 md:flex">
                    <Link
                        v-for="item in links"
                        :key="item.route"
                        :href="route(item.route)"
                        class="focus-ring flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition"
                        :class="
                            route().current(item.route)
                                ? 'bg-campus-blue text-white'
                                : 'text-slate-300 hover:bg-white/8'
                        "
                    >
                        <i :class="item.icon" /> {{ item.label }}
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.is_admin"
                        :href="route('admin.dashboard')"
                        class="focus-ring text-campus-gold flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold"
                        ><i class="pi pi-chart-bar" /> Admin</Link
                    >
                </div>
                <div class="hidden items-center gap-3 md:flex">
                    <div class="text-right">
                        <p class="max-w-42 truncate text-sm font-bold">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-cyan-200/70">
                            {{ $page.props.auth.user.points }} pontos
                        </p>
                    </div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="focus-ring rounded-xl border border-white/15 px-3 py-2 text-sm"
                        >Sair</Link
                    >
                </div>
                <button
                    class="focus-ring rounded-xl border border-white/15 p-3 md:hidden"
                    type="button"
                    aria-label="Abrir menu"
                    @click="menuOpen = !menuOpen"
                >
                    <i :class="menuOpen ? 'pi pi-times' : 'pi pi-bars'" />
                </button>
            </nav>
            <div
                v-if="menuOpen"
                class="border-t border-white/10 px-4 py-4 md:hidden"
            >
                <div class="mx-auto grid max-w-7xl gap-2">
                    <Link
                        v-for="item in links"
                        :key="item.route"
                        :href="route(item.route)"
                        class="rounded-xl bg-white/6 px-4 py-3"
                        @click="menuOpen = false"
                        ><i :class="item.icon" class="mr-2" />
                        {{ item.label }}</Link
                    >
                    <Link
                        v-if="$page.props.auth.user.is_admin"
                        :href="route('admin.dashboard')"
                        class="text-campus-gold rounded-xl bg-white/6 px-4 py-3"
                        >Administração</Link
                    >
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-xl bg-white/6 px-4 py-3 text-left"
                        >Sair</Link
                    >
                </div>
            </div>
        </header>
        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:py-12">
            <slot />
        </main>
        <footer
            class="border-t border-white/8 px-4 py-8 text-center text-xs text-slate-400"
        >
            <div class="mx-auto max-w-3xl">
                <p>{{ $page.props.privacyNotice }}</p>
                <ProjectCredits />
            </div>
        </footer>
    </div>
</template>

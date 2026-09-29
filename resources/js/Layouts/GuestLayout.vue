<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ProjectCredits from '@/Components/ProjectCredits.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="tech-grid bg-campus-navy relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10"
    >
        <div
            class="bg-campus-blue/20 absolute top-10 -left-32 h-80 w-80 rounded-full blur-3xl"
        />
        <div
            class="bg-campus-gold/15 absolute -right-32 bottom-10 h-80 w-80 rounded-full blur-3xl"
        />
        <div class="relative z-10 w-full max-w-lg">
            <Link
                href="/"
                class="focus-ring mx-auto mb-6 block w-64 rounded-2xl sm:w-72"
                ><ApplicationLogo
            /></Link>
            <div class="glass-card rounded-3xl p-6 sm:p-8"><slot /></div>
            <footer class="mt-5 text-center text-xs text-slate-400">
                <p>
                    Seus dados de acesso são usados somente para estatísticas do
                    evento.
                </p>
                <ProjectCredits />
            </footer>
        </div>
    </div>
</template>

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import QRCode from 'qrcode';
import { onMounted, ref } from 'vue';

const props = defineProps({
    games: { type: Array, required: true },
    participants: { type: Number, default: 0 },
    appUrl: { type: String, required: true },
});
const page = usePage();
const qrCode = ref('');
const siCareers = [
    'Software',
    'Web & Mobile',
    'Dados & IA',
    'Cybersecurity',
    'Cloud & DevOps',
    'Produto & Negócios',
];
const adsSkills = [
    'Programação',
    'APIs',
    'Banco de dados',
    'Testes',
    'Arquitetura',
    'Integrações',
];

onMounted(async () => {
    qrCode.value = await QRCode.toDataURL(props.appUrl, {
        width: 360,
        margin: 2,
        color: { dark: '#031329', light: '#ffffff' },
    });
});
</script>

<template>
    <div
        class="tech-grid bg-campus-navy min-h-screen overflow-hidden text-white"
    >
        <Head title="Tecnologia para experimentar" />
        <header
            class="relative z-20 mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6"
        >
            <a href="#inicio" class="focus-ring w-42 rounded-xl sm:w-52"
                ><ApplicationLogo
            /></a>
            <nav class="flex items-center gap-2">
                <Link v-if="page.props.auth.user" :href="route('dashboard')"
                    ><Button
                        label="MEU PAINEL"
                        icon="pi pi-arrow-right"
                        icon-pos="right"
                /></Link>
                <template v-else
                    ><Link
                        :href="route('login')"
                        class="focus-ring rounded-xl px-3 py-2 text-sm font-bold text-slate-200"
                        >Entrar</Link
                    ><Link :href="route('register')"
                        ><Button label="PARTICIPAR" /></Link
                ></template>
            </nav>
        </header>

        <main id="inicio">
            <section
                class="relative mx-auto grid min-h-[78vh] max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:py-20"
            >
                <div
                    class="bg-campus-blue/20 absolute top-0 left-1/3 -z-0 h-96 w-96 rounded-full blur-3xl"
                />
                <div class="relative z-10">
                    <div
                        class="border-campus-cyan/30 bg-campus-cyan/8 text-campus-cyan mb-5 inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold tracking-[.2em] uppercase"
                    >
                        <span
                            class="bg-campus-gold h-2 w-2 animate-pulse rounded-full"
                        />
                        SI + ADS • 2026
                    </div>
                    <h1
                        class="max-w-3xl text-5xl leading-[.95] font-black tracking-tight sm:text-6xl lg:text-7xl"
                    >
                        Conheça tecnologia
                        <span class="gold-text">fazendo.</span>
                    </h1>
                    <p
                        class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-300 sm:text-xl"
                    >
                        Descubra cursos, profissões e possibilidades de carreira
                        enquanto programa um robô, protege sua vida digital e
                        coloca uma rede em ação.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link :href="route('register')"
                            ><Button
                                label="QUERO EXPERIMENTAR"
                                icon="pi pi-bolt"
                                size="large"
                                class="glow-button w-full sm:w-auto"
                        /></Link>
                        <a href="#jogos"
                            ><Button
                                label="VER OS DESAFIOS"
                                icon="pi pi-chevron-down"
                                severity="secondary"
                                outlined
                                size="large"
                                class="w-full sm:w-auto"
                        /></a>
                    </div>
                    <div
                        class="mt-10 flex flex-wrap gap-6 text-sm text-slate-400"
                    >
                        <span
                            ><strong class="text-2xl text-white">3</strong>
                            jogos rápidos</span
                        ><span
                            ><strong class="text-2xl text-white">{{
                                participants
                            }}</strong>
                            participantes</span
                        ><span
                            ><strong class="text-2xl text-white">100%</strong>
                            gratuito</span
                        >
                    </div>
                </div>
                <div class="relative z-10 mx-auto w-full max-w-xl">
                    <div
                        class="bg-campus-blue/30 absolute inset-10 rounded-full blur-3xl"
                    />
                    <img
                        src="/images/campus-tour-logo.png"
                        alt="Campus Tour UniNorte"
                        class="float-slow relative w-full drop-shadow-[0_24px_60px_rgba(8,127,245,.35)]"
                    />
                </div>
            </section>

            <section id="jogos" class="mx-auto max-w-7xl px-4 py-18 sm:px-6">
                <div class="mx-auto max-w-3xl text-center">
                    <span
                        class="text-campus-gold text-sm font-black tracking-[.25em]"
                        >ESCOLHA SUA MISSÃO</span
                    >
                    <h2 class="mt-3 text-4xl font-black sm:text-5xl">
                        Tecnologia que você pode tocar
                    </h2>
                    <p class="mt-4 text-slate-300">
                        Cada desafio ensina um conceito real sem precisar de
                        experiência anterior.
                    </p>
                </div>
                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    <article
                        v-for="(game, index) in games"
                        :key="game.slug"
                        class="glass-card group relative overflow-hidden rounded-3xl p-6"
                    >
                        <div
                            class="absolute -top-12 -right-12 h-32 w-32 rounded-full"
                            :class="
                                index === 1
                                    ? 'bg-campus-gold/15'
                                    : 'bg-campus-blue/20'
                            "
                        />
                        <div
                            class="bg-campus-blue/18 text-campus-cyan relative flex h-14 w-14 items-center justify-center rounded-2xl text-2xl"
                        >
                            <i :class="game.icon" />
                        </div>
                        <p
                            class="text-campus-gold mt-6 text-xs font-bold tracking-widest uppercase"
                        >
                            {{ game.category }}
                        </p>
                        <h3 class="mt-2 text-2xl font-black">
                            {{ game.name }}
                        </h3>
                        <p class="mt-3 leading-relaxed text-slate-300">
                            {{ game.description }}
                        </p>
                        <Link
                            :href="route('register')"
                            class="focus-ring text-campus-cyan mt-6 inline-flex items-center gap-2 rounded-xl text-sm font-bold"
                            >JOGAR AGORA <i class="pi pi-arrow-right"
                        /></Link>
                    </article>
                </div>
            </section>

            <section
                id="cursos"
                class="mx-auto grid max-w-7xl gap-6 px-4 py-18 sm:px-6 lg:grid-cols-2"
            >
                <article class="glass-card rounded-3xl p-7 sm:p-9">
                    <div class="flex items-center gap-4">
                        <span
                            class="bg-campus-blue flex h-14 w-14 items-center justify-center rounded-2xl text-xl font-black"
                            >SI</span
                        >
                        <div>
                            <p
                                class="text-campus-cyan text-xs font-bold tracking-widest"
                            >
                                SISTEMAS DE INFORMAÇÃO
                            </p>
                            <h2 class="text-3xl font-black">
                                Tecnologia + gestão + negócios
                            </h2>
                        </div>
                    </div>
                    <p class="mt-6 leading-relaxed text-slate-300">
                        SI conecta desenvolvimento, sistemas, dados e
                        estratégia. É ideal para quem quer construir tecnologia
                        e também entender como ela transforma organizações e
                        pessoas.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span
                            v-for="career in siCareers"
                            :key="career"
                            class="rounded-full bg-white/7 px-3 py-2 text-sm"
                            >{{ career }}</span
                        >
                    </div>
                </article>
                <article class="glass-card rounded-3xl p-7 sm:p-9">
                    <div class="flex items-center gap-4">
                        <span
                            class="bg-campus-orange flex h-14 w-14 items-center justify-center rounded-2xl text-xl font-black"
                            >ADS</span
                        >
                        <div>
                            <p
                                class="text-campus-gold text-xs font-bold tracking-widest"
                            >
                                ANÁLISE E DESENVOLVIMENTO DE SISTEMAS
                            </p>
                            <h2 class="text-3xl font-black">
                                Foco prático em criar software
                            </h2>
                        </div>
                    </div>
                    <p class="mt-6 leading-relaxed text-slate-300">
                        ADS mergulha no desenvolvimento de sistemas, Web,
                        Mobile, bancos de dados, APIs, testes e integração. É
                        uma formação direta para quem quer colocar soluções
                        digitais em produção.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span
                            v-for="skill in adsSkills"
                            :key="skill"
                            class="rounded-full bg-white/7 px-3 py-2 text-sm"
                            >{{ skill }}</span
                        >
                    </div>
                </article>
                <p class="text-center text-sm text-slate-400 lg:col-span-2">
                    Os dois cursos abrem portas para tecnologia. SI tem formação
                    mais ampla entre tecnologia e negócios; ADS é mais curto e
                    concentrado na construção de sistemas. A melhor escolha
                    depende do seu jeito de aprender e dos seus objetivos.
                </p>
            </section>

            <section class="mx-auto max-w-5xl px-4 py-18 sm:px-6">
                <div
                    class="glass-card border-campus-gold/25 grid items-center gap-8 rounded-3xl p-7 sm:p-10 md:grid-cols-[1fr_auto]"
                >
                    <div>
                        <span
                            class="text-campus-gold text-sm font-black tracking-[.2em]"
                            >LEVE O DESAFIO COM VOCÊ</span
                        >
                        <h2 class="mt-3 text-4xl font-black">
                            Aponte a câmera e entre
                        </h2>
                        <p class="mt-3 max-w-xl text-slate-300">
                            O QR Code abre esta experiência no seu celular.
                            Cadastre-se, jogue e veja sua posição no ranking.
                        </p>
                        <Link
                            :href="route('register')"
                            class="mt-6 inline-block"
                            ><Button
                                label="ENTRAR NO CAMPUS TOUR"
                                icon="pi pi-arrow-right"
                                icon-pos="right"
                                size="large"
                        /></Link>
                    </div>
                    <div class="mx-auto rounded-3xl bg-white p-4">
                        <img
                            v-if="qrCode"
                            :src="qrCode"
                            alt="QR Code para acessar o Campus Tour"
                            class="h-52 w-52"
                        />
                    </div>
                </div>
            </section>
        </main>

        <footer
            class="border-t border-white/8 px-4 py-10 text-center text-sm text-slate-400"
        >
            <p>
                Campus Tour UniNorte 2026 • Sistemas de Informação & Análise e
                Desenvolvimento de Sistemas
            </p>
            <p class="mt-2 text-xs">
                Coletamos somente dados mínimos de acesso para estatísticas do
                evento. Não solicitamos documentos pessoais.
            </p>
        </footer>
    </div>
</template>

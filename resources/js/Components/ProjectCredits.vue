<template>
    <div
        class="mt-5 flex flex-col items-center justify-center gap-3 border-t border-white/8 pt-5 sm:flex-row sm:gap-4"
    >
        <a
            href="https://juancjc.com.br"
            target="_blank"
            rel="noopener noreferrer"
            class="focus-ring group inline-flex items-center gap-3 rounded-2xl px-2 py-1.5 transition hover:bg-white/5"
            aria-label="Conhecer o portfólio do professor JUANCJC"
        >
            <img
                src="/images/juancjc-github.jpg"
                alt="Professor JUANCJC"
                class="border-campus-cyan/50 h-11 w-11 rounded-full border-2 object-cover transition group-hover:scale-105 group-hover:border-cyan-200"
            />
            <span class="text-left leading-tight">
                <span class="block text-[0.68rem] text-slate-400"
                    >Feito pelo professor</span
                >
                <strong class="text-campus-cyan text-sm tracking-wide"
                    >JUANCJC</strong
                >
            </span>
        </a>

        <a
            href="https://github.com/Juancjc/campus-tour-uninorte"
            target="_blank"
            rel="noopener noreferrer"
            class="focus-ring hover:border-campus-cyan/40 inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-bold text-slate-300 transition hover:bg-white/10 hover:text-white"
            aria-label="Abrir o repositório do Campus Tour no GitHub"
        >
            <i class="pi pi-github text-base" aria-hidden="true" />
            Repositório do projeto
            <i class="pi pi-external-link text-[0.65rem]" aria-hidden="true" />
        </a>
    </div>
</template>

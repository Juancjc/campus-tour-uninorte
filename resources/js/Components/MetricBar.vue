<script setup>
import { computed } from 'vue';
const props = defineProps({
    items: { type: Array, required: true },
    color: { type: String, default: 'blue' },
});
const maximum = computed(() =>
    Math.max(...props.items.map((item) => item.value), 1),
);
</script>

<template>
    <div class="grid gap-3">
        <div
            v-for="item in items"
            :key="item.label"
            class="grid grid-cols-[7rem_1fr_auto] items-center gap-3 text-sm"
        >
            <span class="truncate text-slate-300">{{ item.label }}</span>
            <div class="h-2 overflow-hidden rounded-full bg-white/7">
                <div
                    class="h-full rounded-full"
                    :class="
                        color === 'gold' ? 'bg-campus-gold' : 'bg-campus-cyan'
                    "
                    :style="{
                        width: `${Math.max(4, (item.value / maximum) * 100)}%`,
                    }"
                />
            </div>
            <strong>{{ item.value }}</strong>
        </div>
    </div>
</template>

<script setup>
defineProps({ compact: { type: Boolean, default: false } });
</script>

<template>
    <img
        src="/images/campus-tour-logo.png"
        alt="Campus Tour UniNorte 2026 — SI e ADS"
        :class="compact ? 'h-12 w-auto' : 'h-auto w-full'"
    />
</template>

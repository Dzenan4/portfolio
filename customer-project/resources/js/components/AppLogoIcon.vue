<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg width="40" height="42" viewBox="0 0 60 60">
        <image x="0" y="0" width="60" height="60" href="@/assets/appLogo.png" />
    </svg>
</template>

<script setup>

import { Head } from '@inertiajs/vue3';

defineProps({
    inertiaVariable: {
        type: String,
    },
});
</script>

<template>
    <Head title="Dashboard" />
</br>
    I wonder if this works
    {{  inertiaVariable }}
</template>
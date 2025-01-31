<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';

const props = defineProps({
    groups: {
        type: Object,
    },
});

onMounted(() => console.log(props.groups));


</script>

<template>
    <div>
        groups
    </div>
</template>
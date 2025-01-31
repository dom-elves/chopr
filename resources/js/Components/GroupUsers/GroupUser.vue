<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
const props = defineProps({
    group_user: {
        type: Object,
    },
});

onMounted(() => console.log(props.group_user));


</script>

<template>
    <div>
        {{ group_user.user.name }}
    </div>
</template>
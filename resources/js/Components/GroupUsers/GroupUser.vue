<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
const props = defineProps({
    group_user: {
        type: Object,
    },
});

</script>

<template>
    <div class="my-2 border-solid border-2 border-amber-600">
        <p class="p-2 text-xl w-full text-center w-full">
            {{ group_user.user.name }}
        </p>
    </div>
</template>
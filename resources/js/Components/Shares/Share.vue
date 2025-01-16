<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    share: {
        type: Object,
    },
});

</script>

<template>
    <div 
        class="p-1 m-2 border-solid border-2" 
        :class="share.cleared ? 'border-emerald-600' : 'border-red-600'"
    >
        <p>{{ share.group_user.user.name }}</p>
        <p>{{ share.amount }}</p>
    </div>
</template>

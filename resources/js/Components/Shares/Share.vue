<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    share: {
        type: Object,
    },
});

onMounted(() => console.log(props.share));

</script>

<template>
    <div class="p-1 m-2 border-solid border-2 border-emerald-600">
        <p>{{ share.group_user.user.name }}</p>
        <p>{{ share.amount }}</p>
    </div>
</template>

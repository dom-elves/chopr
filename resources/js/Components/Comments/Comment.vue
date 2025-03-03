<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    comment: {
        type: Object,
    },
});
</script>
<template>
    <div>
        {{ comment.content }}
    </div>
</template>
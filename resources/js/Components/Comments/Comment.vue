<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    comment: {
        type: Object,
    },
});

const options = { 
    year: "numeric", 
    month: "long", 
    day: "numeric", 
    hour: "2-digit", 
    minute: "2-digit", 
};

onMounted(() => {
    console.log(usePage().props);
});
</script>
<template>
    <div class="p-1 my-2 border-solid border-2">
        <div class="flex flex-row justify-between">
            <div>
                <strong>{{ comment.user.name }}</strong>
                <small> on {{ new Date(comment.created_at).toLocaleDateString("en-GB", options) }}</small>
            </div>
            <div
                v-if="usePage().props.ownership.comment_ids.includes(props.comment.id)"
                class="p-2 flex flex-row justify-between">
                <i 
                    class="fa-solid fa-gear mx-1"
                >
                </i>
                <i 
                    class="fa-solid fa-x mx-1"
                >
                </i>
            </div>
        </div>
        <p>{{ comment.content }}</p>
    </div>
</template>
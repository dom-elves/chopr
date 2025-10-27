<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    message: {
        type: String,
    },
});

/**
 * There's likely a better way of achieving this 
 * So the page is returned which extends AuthenticatedLayout
 * And AuthenticatedLayout contains the toast so that's the only place it needs to exist
 * Just need to pass the controller method return 'status' between props
 * So should be quite future-proof
 */
onMounted(() => {
    setTimeout(() => {
        const toast = document.getElementById('toast');
        toast.style.display = 'flex';
    }, 1000)
})

watch(
    () => props.message,
    (newMessage) => {
        if (newMessage) {
            const toast = document.getElementById('toast');
            toast.style.display = 'flex';

            setTimeout(() => {
                toast.style.display = 'hidden';
            }, 1000)
        }
    },
);
</script>
<template>
    <div id="toast" class="toast">
        <p class="p-6 text-2xl text-green-300">
            {{ props.message }}
        </p>
    </div>
</template>
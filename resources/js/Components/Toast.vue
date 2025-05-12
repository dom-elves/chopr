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
        toast.style.display = 'none';
    }, 1000)
})
</script>
<template>
    <div
        id="toast"
        class="flex my-2 mt-16 w-full border-solid border-4 rounded border-green-300 bg-white z-10 fixed justify-center" style="height:100px">
        <p class="p-6 text-3xl text-green-300">
            {{ props.message }}
        </p>
    </div>
</template>
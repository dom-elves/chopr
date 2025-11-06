<script setup>
import { ref, onMounted, watch} from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    message: {
        type: String,
    },
});

const showToast = ref(false);

/**
 * There's likely a better way of achieving this 
 * So the page is returned which extends AuthenticatedLayout
 * And AuthenticatedLayout contains the toast so that's the only place it needs to exist
 * Just need to pass the controller method return 'status' between props
 * So should be quite future-proof
 */
onMounted(() => {

});

watch(
    () => usePage().props.flash.status,
    (newMessage) => {
        showToast.value = true;

        setTimeout(() => {
            showToast.value = false;
            usePage().props.flash.status = null;
        }, 3000);
    }
);
</script>
<template>
    <div class="toast-wrapper md:w-1/2 p-2">
        <div v-show="showToast" id="toast" class="toast">
            <p class="p-6 text-2xl text-green-300">
                {{ usePage().props.flash.status }}
            </p>
        </div>
    </div>
</template>
<style>

.toast-wrapper {
    position:fixed;
    display:flex;
    justify-content:center;
    align-items:center;
    pointer-events:none;
    z-index:1000;
} 

.toast {
    display: flex;
    height: 100px;
    width: 100%;
    top: 100px;
    background-color: white;
    border: #9cef60 2px solid;
    border-radius: 15px;
    position: sticky;
    box-shadow: 0 4px 12px #9cef60;
    animation: toastFade 3s ease-in-out forwards;
}

.toast > p {
    color: #9cef60;
    font-weight: bold;
}

@keyframes toastFade {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }
  20% {
    opacity: 1;
    transform: translateY(0);
  }
  70% {
    opacity: 1;
    transform: translateY(0);
  }
  100% {
    opacity: 0;
    transform: translateY(-20px);
  }
}
</style>
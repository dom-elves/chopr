<script setup>
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';

const openModal = ref(false);

</script>
<template>
    <div>
        <button 
            class="bg-blue-400 text-white p-2 w-full" 
            @click="openModal = !openModal"
            >
                Invite
        </button>
        <Modal 
            :show="openModal" 
            :closeable="true" 
            @close="openModal = !openModal"
            >
                <p>some stuff</p>
        </Modal>
    </div>
</template>
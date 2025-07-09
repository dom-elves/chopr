<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { recipients } from '@/invite.js';
import Modal from '@/Components/Modal.vue';

const openModal = ref(false);
const emailInput = ref('');

function addRecipient() {
    recipients.emailAddresses.push(emailInput.value);
}

function removeRecipient(emailAddress) {
    recipients.emailAddresses = recipients.emailAddresses.filter((email) => email !== emailAddress);
}

</script>

<template>
    <div>
        <input
            type="email"
            v-model="emailInput"
            @keydown.enter.prevent="addRecipient"
        >
        <span v-for="emailAddress in recipients.emailAddresses">
            {{ emailAddress }}
            <i
                class="fa-solid fa-x mx-1"
                @click="removeRecipient(emailAddress)"
            ></i>
        </span>
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
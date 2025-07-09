<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const openModal = ref(false);
const recipient = ref('');

const invite = useForm({
    recipients: [],
    body: '',
})

function addRecipient() {
    invite.recipients.push(recipient.value);
    // add validation to prevent dupes
    recipient.value = '';
}

function removeRecipient(emailAddress) {
    invite.recipients = invite.recipients.filter((email) => email !== emailAddress);
}

function sendInvite() {
    console.log(invite);
}

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
            <form @submit.prevent="sendInvite">
                <div>
                    <input
                        type="email"
                        v-model="recipient"
                        @keydown.enter.prevent="addRecipient"
                        placeholder="Enter email"
                    >
                    <span v-for="recipient in invite.recipients">
                        {{ recipient }}
                        <i
                            class="fa-solid fa-x mx-1"
                            @click="removeRecipient(recipient)"
                        ></i>
                    </span>
                </div>
                <div>
                    <textarea
                        type="text"
                        v-model="invite.body"
                        placeholder="Add a message to your invite"
                    >
                    </textarea>
                </div>
                <button type="submit">Send</button>
            </form>
        </Modal>
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const openModal = ref(false);
const recipient = ref('');

const props = defineProps({
    group: {
        type: Object,
    },
});

const invite = useForm({
    group_id: props.group.id,
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
    invite.post(route('invite.send'), {
        onSuccess: (result) => {
            console.log('r', result);
        },
        onError: (error) => {
            console.log('e', error);
        },
    })
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
        <div class="p-6">
            <form @submit.prevent="sendInvite">
                <div class="mb-4">
                    <h2
                        class="text-lg font-medium text-gray-900"
                    >
                        Enter the addresses of who you wish to invite:
                    </h2>
                    <input
                        type="email"
                        v-model="recipient"
                        @keydown.enter.prevent="addRecipient"
                        placeholder="Enter email & press enter"
                    >
                    
                </div>
                <div>
                    <span 
                        v-for="recipient in invite.recipients"
                        class="items-center rounded-md border border-black p-1 bg-gray-900 text-white font-semibold m-1"
                        style="display:inline-block"
                        >
                        {{ recipient }}
                        <i
                            class="fa-solid fa-x mx-1 fa-xs"
                            @click="removeRecipient(recipient)"
                        ></i>
                    </span>
                </div>
                <div class="mb-4">
                    <h2
                        class="text-lg font-medium text-gray-900"
                    >
                        And a message for them:
                    </h2>
                    <textarea
                        type="text"
                        v-model="invite.body"
                        placeholder="Add a message to your invite"
                    >
                    </textarea>
                </div>
 
                <PrimaryButton
                    type="submit"
                >
                    Send
                </PrimaryButton>
            </form>
        </div>
        </Modal>
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const openModal = ref(false);
const recipient = ref('');
const mailRegex = ref(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);

const props = defineProps({
    group: {
        type: Object,
    },
});

const inviteForm = useForm({
    group_id: props.group.id,
    user_id: usePage().props.auth.user.id,
    recipients: [],
    body: '',
})

function addRecipient() {
    const valid = mailRegex.value.test(recipient.value);

    // refactor this so have a fe error for duplicate emails
    if (valid) {
        inviteForm.recipients.push(recipient.value);
        recipient.value = '';
    } else {
        inviteForm.errors.recipients = ['Not a valid email address'];
    }

}

function removeRecipient(emailAddress) {
    inviteForm.recipients = inviteForm.recipients.filter((email) => email !== emailAddress);
}

function sendInviteForm() {
    console.log(inviteForm);
    inviteForm.post(route('invite.send'), {
        onSuccess: (result) => {
            console.log('r', result);
            openModal.value = false;
        },
        onError: (error) => {
            console.log('e', error);

            // filter all error messages that have recipients as the key
            const recipients = Object.fromEntries(
                Object.entries(error).filter(([key]) =>
                        key.includes('recipients')
                    )
                );
     
            // turn the messages into an array to  be looped over
            // as recipients is the only field that can return multiple errors at once
            inviteForm.errors.recipients = Object.values(recipients);
        },
    })
}

function clearEmailInput() {
    recipient.value = '';
    inviteForm.errors.recipients = '';
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
            <form @submit.prevent="sendInviteForm">
                <div class="mb-4">
                    <h2
                        class="text-lg font-medium text-gray-900"
                    >
                        Enter the addresses of who you wish to invite:
                    </h2>
                    <input
                    class="w-3/4"
                        type="email"
                        v-model="recipient"
                        @keydown.enter.prevent="addRecipient"
                        placeholder="Enter email & press enter"
                        style="border-right:none"
                    >
                    <button
                        type="button"
                        class="px-2"
                        style="height:42px;border-color:#6b7280;border-width:1px;border-left:none;">
                        <i
                            class="fa-solid fa-x mx-1 fa-xs"
                            @click="clearEmailInput()"
                        ></i>
                    </button>
                    <InputError class="mt-2"v-for="error in inviteForm.errors.recipients" :message="error" />
                </div>
                <div class="mb-4">
                    <span 
                        v-for="recipient in inviteForm.recipients"
                        class="items-center rounded-md border border-black p-1 bg-gray-900 text-white font-semibold my-1 mr-1"
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
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        type="text"
                        v-model="inviteForm.body"
                        placeholder="Add a message to your invite"
                    >
                    </textarea>
                    <InputError class="mt-2" :message="inviteForm.errors.body" />
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
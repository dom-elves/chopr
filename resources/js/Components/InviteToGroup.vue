<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import BigButton from '@/Components/BigButton.vue';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';

const openModal = ref(false);
const mailRegex = ref(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);
const recipients = ref([]);

// these are specifically to deal with entering an email in the input outside of the form
// even though the errors appear in the same place
// nothing is submitted on email enter, it's just building an array
const recipient = ref('');
const recipientError = ref('');

const props = defineProps({
    group: {
        type: Object,
    },
});

function addRecipient(recipientEmail) {
    
    if (recipientEmail === '') {
        recipientError.value = 'Please enter an email address.';
    } else if (!mailRegex.value.test(recipientEmail)) {
        recipientError.value = `'${recipientEmail}' is not a valid email address.`;
    } else if (recipients.value.includes(recipientEmail)) {
        recipientError.value = `'${recipientEmail}' has already been added.`;
    } else {
        recipients.value.push(recipientEmail);
        recipient.value = '';
        recipientError.value = '';
    }
}

function removeRecipient(emailAddress) {
    recipients.value = recipients.value.filter((email) => email !== emailAddress);
}

</script>

<template>
    <div>
        <BigButton 
            @click="openModal = !openModal"
        >
            Invite
        </BigButton>
        <Modal 
            :show="openModal" 
            :closeable="true" 
            @close="openModal = !openModal"
        >     
        <div class="p-6">
            <!-- input for building email array -->
            <div class="mb-4 w-full">
                <label
                    for="recipient"
                    class="text-lg font-medium text-gray-900"
                >
                    Enter the addresses of who you wish to invite:
                </label>
                <input
                    id="recipient"
                    class="rounded-l-md border-gray-300 shadow-sm"
                    type="email"
                    name="recipient"
                    v-model="recipient"
                    @keydown.enter.prevent="addRecipient($event.target.value)"
                    placeholder="Enter email & press enter"
                    style="border-right:none;width:calc(100% - 37px)"
                >
                <button
                    type="button"
                    class="px-2 rounded-r-md border border-gray-300 shadow-sm"
                    style="height:42px;border-left:none"
                    @click="recipient = ''"
                    >
                    <i
                        class="fa-solid fa-x mx-1 fa-xs"
                    ></i>
                </button>
            </div>
            <InputError class="mt-2" :message="recipientError" />
            <!-- actual form for submission -->
            <Form
                :action="route('invite.send')" 
                method="post" 
                #default="{ errors }"
                @success="openModal = false"
                resetOnSuccess
                :transform="data => ({ 
                    ...data, 
                    recipients: recipients,
                    group_id: props.group.id,
                    user_id: usePage().props.auth.user.id,
                })"
            >
                <!-- recipient badges -->
                <div class="mb-4">
                    <span 
                        v-for="recipient in recipients"
                        class="items-center rounded-md border border-black p-1 bg-gray-900 text-white font-semibold my-1 mr-1"
                        style="display:inline-block;word-break:break-word"
                        >
                        {{ recipient }}
                        <i
                            class="fa-solid fa-x mx-1 fa-xs"
                            @click="removeRecipient(recipient)"
                        ></i>
                    </span>
                </div>
                <!-- just so this appears to be an error for the mail input field -->
                <div v-for="(error, key) in errors" class="mb-4">
                    <InputError v-if="key != 'body'" class="mt-2" :message="error" />
                </div>
                <!-- message -->
                <div class="mb-4">
                    <label
                        class="text-lg font-medium text-gray-900"
                    >
                        And a message for them:
                    </label>
                    <textarea
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        type="text"
                        name="body"
                        placeholder="Add a message to your invite"
                    >
                    </textarea>
                    <InputError class="mt-2" v-if="errors.body" :message="errors.body" />
                </div>
                <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                    <SecondaryButton 
                        type="button"
                        @click="openModal = false"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        type="submit"
                    >
                        Send
                    </PrimaryButton>
                </div>
            </Form>
        </div>
        </Modal>
    </div>
</template>

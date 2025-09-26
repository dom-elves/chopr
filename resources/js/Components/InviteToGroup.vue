<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
        recipientError.value = 'Please enter an email address';
    } else if (!mailRegex.value.test(recipientEmail)) {
        recipientError.value = `'${recipientEmail}' is not a valid email address`;
    } else if (recipients.value.includes(recipientEmail)) {
        recipientError.value = `'${recipientEmail}' has already been added`;
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
            <!-- input for building email array -->
            <div class="mb-4">
                <label
                    for="recipient"
                    class="h2 text-lg font-medium text-gray-900"
                >
                    Enter the addresses of who you wish to invite:
                </label>
                <input
                    id="recipient"
                    class="w-3/4"
                    type="email"
                    name="recipient"
                    v-model="recipient"
                    @keydown.enter.prevent="addRecipient($event.target.value)"
                    placeholder="Enter email & press enter"
                    style="border-right:none"
                >
                <button
                    type="button"
                    class="px-2"
                    style="height:42px;border-color:#6b7280;border-width:1px;border-left:none;"
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
                <InputError class="mt-2" v-for="error in errors" :message="error" />
                <!-- recipient badges -->
                <div class="mb-4">
                    <span 
                        v-for="recipient in recipients"
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
                <!-- message -->
                <div class="mb-4">
                    <label
                        class="h2 text-lg font-medium text-gray-900"
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
                    <InputError class="mt-2" :message="errors.body" />
                </div>
                <PrimaryButton
                    type="submit"
                >
                    Send
                </PrimaryButton>
            </Form>
        </div>
        </Modal>
    </div>
</template>
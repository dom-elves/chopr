<script setup>
import { computed, onMounted, ref, watch, reactive } from 'vue';
import { currencies } from '@/currencies.js';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Controls from '@/Components/Controls.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    share: {
        type: Object,
    },
    currency: {
        type: String,
    },
    debt: {
        type: Object,
    },
});


const confirmingShareDeletion = ref(false);
// if the logged in user owners the debt, display the controls
const displayControls = usePage().props.auth.user.id === props.debt.user_id ? true : false;
// if the share on display is for the owner of the debt, highlight it
const isDebtOwner = props.share.user_id === props.debt.user_id;
const isEditing = ref(false);

// send share
const sendShareForm = useForm({
    id: props.share.id,
    debt_id: props.share.debt_id,
    sent: props.share.sent,
});

function sendShare() {
    // change the status of the checkbox, post it
    sendShareForm.sent = !sendShareForm.sent;
    sendShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            
        },
        onError: (error) => {
   
        },
    });
}

// seen share
const seenShareForm = useForm({
    id: props.share.id,
    debt_id: props.share.debt_id,
    seen: props.share.seen,
});

function seenShare() {
    // change the status of the checkbox, post it
    seenShareForm.seen = !seenShareForm.seen;
    seenShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            
        },
        onError: (error) => {

        },
    });
}

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

</script>

<template>
    <div class="plate" :style="isDebtOwner ? 'box-shadow: 2px 2px green' : ''">
        <div v-if="!isEditing" class="flex flex-row justify-between w-full">
            <!-- group user, share name, amount -->
            <div class="flex flex-col">
                <p class="font-semibold">{{ share.group_user.user.name }}</p>
                <p>{{ debtCurrency.symbol }}{{ share.amount.amount }}</p>
                <p>{{ share.name ? share.name : ' ' }}</p>
            </div>
            <div class="flex flex-row items-center">
                <!-- sent & seen -->
                <div class="flex flex-row items-center" :class="!isDebtOwner ? 'visible' : 'invisible'">
                    <form class="flex flex-col items-center p-1" @submit.prevent="sendShare">
                        <small>Sent</small>
                        <label 
                            class="hidden"
                            :for="'sent-' + share.id"
                        >
                            Send share
                        </label>
                        <input 
                            type="checkbox" 
                            :id="'sent-' + share.id" 
                            class="hidden"
                            v-model="sendShareForm.sent"
                        >
                        <button
                            style="height:40px;width:40px;border-radius:50%" 
                            class="border-solid border-2 flex justify-center items-center"
                            :class="props.share.sent ? 'border-green-400' : 'border-red-400'"
                        >
                            <i class="fa-solid fa-check"></i>
                        </button>
                        <InputError class="mt-2" :message="sendShareForm.errors.sent" />
                    </form>
                    <form class="flex flex-col items-center p-1" @submit.prevent="seenShare">
                        <small>Seen</small>
                        <label 
                            class="hidden"
                            :for="'seen-' + share.id"
                        >
                            Seen share
                        </label>
                        <input 
                            type="checkbox" 
                            :id="'seen-' + share.id" 
                            class="hidden"
                            v-model="seenShareForm.seen"
                        >
                        <button
                            style="height:40px;width:40px;border-radius:50%" 
                            class="border-solid border-2 flex justify-center items-center"
                            :class="props.share.seen ? 'border-green-400' : 'border-red-400'"
                        >
                            <i class="fa-solid fa-check"></i>
                        </button>
                        <InputError class="mt-2" :message="seenShareForm.errors.seen" />
                    </form>
                </div>
                <Controls
                    :class="displayControls && !isEditing ? '' : 'invisible'"
                    :key="props.share.id"
                    item="Share"
                    @edit="isEditing = !isEditing"
                    @destroy="confirmingShareDeletion = true"
                >
                </Controls>
            </div>
        </div>
        <div v-else class="w-full">
            <Form
                :action="route('share.update')" 
                method="patch" 
                #default="{ errors }"
                :transform="data => ({
                    ...data,
                    id: props.share.id,
                    debt_id: props.debt.id, 
                })"
                @success="isEditing = false"
                :options="{
                    preserveScroll: true,
                }"
            >
                <div class="flex flex-col">
                    <div class="flex flex-row">
                        <label 
                            for="newShareName" 
                            style="display:none;"
                            id="newShareNameLabel"
                        >
                        New Name
                        </label>
                        <TextInput
                            name="name"
                            type="text"
                            id="newShareName"
                            aria-labelledby="newShareNameLabel"
                            placeholder="Enter a new share name..."
                            class="w-full"
                            style="height:48px"
                        />
                    </div>
                    <InputError class="mt-2" :message="errors.name" />
                    <div class="flex flex-row">
                        <label 
                            for="ShareAmount"
                            style="display:none;"
                            id="newShareAmountLabel
                        ">
                        New Amount
                        </label>
                        <TextInput 
                            name="amount"
                            type="number"
                            step="0.01"
                            id="newShareAmount"
                            placeholder="Enter a new amount..."
                            aria-labelledby="newShareAmountLabel"
                            class="w-full mt-2"
                            style="height:48px"
                        />
                    </div>
                    <InputError class="mt-2" :message="errors.amount" />
                    <div class="flex flex-row mt-2">
                        <SecondaryButton
                            type="button"
                            class="w-1/2 justify-center mr-2"
                            @click="isEditing = false"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            type="submit"
                            class="w-1/2 justify-center"
                        >
                            Save
                        </PrimaryButton>
                    </div>
                </div>
            </Form>
        </div>
        <Modal :show="confirmingShareDeletion">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this Share?
                </h2>
                <Form
                    class="mt-6 flex justify-end"
                    :action="route('share.destroy')"
                    method="delete"
                    #default="{ errors }"
                    @success="confirmingShareDeletion = false"
                    :options="{
                        preserveScroll: true,
                    }"
                    :transform="data => ({ 
                        ...data, 
                        debt_id: props.share.debt_id,
                    })"
                >
                    <div class="flex flex-row mt-4 justify-center sm:justify-end w-full">
                        <SecondaryButton 
                            @click="confirmingShareDeletion = false;"
                        >
                            Cancel
                        </SecondaryButton>
                        <input
                            type="hidden"
                            name="id"
                            :value="props.share.id"
                        />
                        <DangerButton
                        >
                            Delete
                        </DangerButton>
                        <InputError class="mt-2 content-end" :message="errors.id" />
                    </div>
                </Form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch, reactive } from 'vue';
import { currencies } from '@/currencies.js';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Controls from '@/Components/Controls.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';

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

// update share
const updateShareForm = useForm({
    id: props.share.id,
    debt_id: props.share.debt_id,
    amount: props.share.amount.amount,
    name: props.share.name,
});

function updateShare() {
    updateShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: (response) => {
            isEditing.value = !isEditing.value;
            // todo: add an event to send to Debt so discrepancy notice is updated (or not?)
        },
        onError: (error) => {

        },
    });
}

// delete share
const deleteShareForm = useForm({
    id: props.share.id,
    debt_id: props.share.debt_id,
});

function deleteShare() {
    console.log(deleteShareForm);
    deleteShareForm.delete(route('share.destroy'), {
        preserveScroll: true,
        onSuccess: (response) => {
            confirmingShareDeletion.value = false;
        },
        onError: (error) => {

        },
    });
}

onMounted(() => {

});

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

const closeModal = () => {
    confirmingShareDeletion.value = false;
};

</script>

<template>
    <div 
        class="p-1 my-2 border-solid border-2 w-full  flex flex-col" 
        :class="isDebtOwner ? 'border-green-400' : ''">
        <div 
            class="flex flex-row justify-between"
        >
        {{  share.id }}
            <div
                v-if="!isEditing" 
                class="flex-col flex-start" 
    
                style="height:70px"
            >
                <p>
                    {{ share.group_user.user.name }} {{ share.id }}
                    <small v-if="isDebtOwner">
                        owner
                    </small>
                </p>
                <p>{{  share.name }}</p>
                <p>{{ debtCurrency.symbol }}{{ share.amount.amount }}</p>
            </div>
            <form 
                v-else
                class="flex flex-row">
                <label 
                    for="shareAmount"
                    style="display:none;"
                    id="newshareAmountLabel
                ">
                New Amount
                </label>
                <input 
                    type="number"
                    step="0.01"
                    id="newshareAmount"
                    aria-labelledby="newshareAmountLabel"
                    v-model="updateShareForm.amount"
                    @blur="updateShare"
                >
                <label 
                    for="shareName"
                    style="display:none;"
                    id="newshareNameLabel"
                >
                New Name
                </label>
                <input 
                    type="text"
                    id="newShareLabel"
                    aria-labelledby="newShareNameLabvel"
                    v-model="updateShareForm.name"
                    :placeholder="updateShareForm.name"
                    @blur="updateShare"
                >
                <InputError class="mt-2" :message="updateShareForm.errors.id" />
                <InputError class="mt-2" :message="updateShareForm.errors.amount" />
            </form>

            <div 
                v-if="!isDebtOwner"
                class="flex flex-row"
            >
                <div>
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
                    </form>
                </div>
                <div>
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
                    </form>
                </div>
            </div>
            <Controls
                v-if="displayControls"
                :key="props.share.id"
                item="Share"
                @edit="isEditing = !isEditing"
                @destroy="confirmingShareDeletion = true"
            >
            </Controls>
        </div>
        <InputError class="mt-2" :message="sendShareForm.errors.sent" />
        <InputError class="mt-2" :message="seenShareForm.errors.seen" />
        <Modal :show="confirmingShareDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this Share?
                </h2>
                <InputError class="mt-2" :message="deleteShareForm.errors.id" />
                <div class="mt-6 flex justify-end">
                    <button 
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        class="ms-3"
                        @click="deleteShare"
                    >
                        Delete Share
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch, reactive } from 'vue';
import { currencies } from '@/currencies.js';
import { router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    share: {
        type: Object,
    },
    currency: {
        type: String,
    },
    debtOwnerId: {
        type: Number,
    },
});

// check if logged in user is debt owner
const isDebtOwner = props.debtOwnerId === props.share.group_user_id;
// check if logged in user is share owner
const isShareOwner = props.share.group_user.user.id === usePage().props.auth.user.id;

// send share
const sendShareForm = useForm({
    id: props.share.id,
    sent: props.share.sent,
});

const sendShareFormErrors = reactive({
    id: null,
    sent: null,
});

function sendShare() {
    // change the status of the checkbox, post it
    sendShareForm.sent = !sendShareForm.sent;
    sendShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            
        },
        onError: (error) => {
            sendShareFormErrors.id = error.id;
            sendShareFormErrors.sent = error.sent;
        },
    });
}

// seen share
const seenShareForm = useForm({
    id: props.share.id,
    seen: props.share.seen,
});

const seenShareFormErrors = reactive({
    id: null,
    seen: null,
});

function seenShare() {
    // change the status of the checkbox, post it
    seenShareForm.seen = !seenShareForm.seen;
    seenShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            
        },
        onError: (error) => {
            seenShareFormErrors.id = error.id;
            seenShareFormErrors.seen = error.seen;
        },
    });
}

onMounted(() => {
    // console.log(props.share.id, props.share.sent, props.share.seen);
});

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

</script>

<template>
    <div 
        class="p-1 my-2 border-solid border-2 w-full  flex flex-col" 
        :class="isDebtOwner ? 'border-green-400' : ''">
        <div 
            class="flex flex-row justify-between"
        >
            <div 
                class="flex-col flex-start" 
    
                style="height:70px"
            >
                <p>
                    {{ share.group_user.user.name }} {{ share.id }}
                    <small v-if="isDebtOwner">
                        owner
                    </small>
                </p>
                <p>{{ debtCurrency.symbol }}{{ share.amount }}</p>
            </div>

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
        </div>
        <div v-if="sendShareFormErrors">
            <p v-for="error in sendShareFormErrors" class="text-red-500">{{ error }}</p>
        </div>
        <div v-if="seenShareFormErrors">
            <p v-for="error in seenShareFormErrors" class="text-red-500">{{ error }}</p>
        </div>
    </div>
</template>

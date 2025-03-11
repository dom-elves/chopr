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

// consts taken from db
let isShareSent = ref(props.share.sent ? true : false);
let isShareSeen = ref(props.share.seen ? true : false);

//
const sendShareForm = useForm({
    id: props.share.id,
    sent: isShareSent.value,
    group_user_id: props.share.group_user_id,
});

const sendShareFormErrors = reactive({
    id: null,
    sent: null,
    group_user_id: null,
});

function sendShare() {
    console.log('form', sendShareForm);
    sendShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            sendShareForm.sent = !sendShareForm.sent;
        },
        onError: (error) => {
            sendShareFormErrors.id = error.id;
            sendShareFormErrors.sent = error.sent;
            sendShareFormErrors.group_user_id = error.group_user_id;
        },
    });
}

const seenShareForm = useForm({
    id: props.share.id,
    seen: isShareSeen.value,
    group_user_id: props.share.group_user_id,
});

const seenShareFormErrors = reactive({
    id: null,
    seen: null,
    group_user_id: null,
});

function seenShare() {
    console.log('form', seenShareForm);
    seenShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            seenShareForm.seen = !seenShareForm.seen;
        },
        onError: (error) => {
            seenShareFormErrors.id = error.id;
            seenShareFormErrors.seen = error.seen;
            seenShareFormErrors.group_user_id = error.group_user_id;
        },
    });
}

onMounted(() => {
    // console.log(isDebtOwner);
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
                            :class="sendShareForm.sent ? 'border-green-400' : 'border-red-400'"
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
                            :class="seenShareForm.seen ? 'border-green-400' : 'border-red-400'"
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

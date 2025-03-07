<script setup>
import { computed, onMounted, ref, watch, } from 'vue';
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

let isShareSent = ref(props.share.sent ? true : false);
let isShareSeen = ref(props.share.seen ? true : false);

const sendShareForm = useForm({
    id: props.share.id,
    sent: isShareSent.value,
    group_user_id: props.share.group_user_id,
});

function sendShare() {
    console.log('form', sendShareForm);
    sendShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('sent');
        },
    });
}

const seenShareForm = useForm({
    id: props.share.id,
    seen: isShareSeen.value,
    group_user_id: props.share.group_user_id,
});

function seenShare() {
    console.log('form', seenShareForm);
    seenShareForm.patch(route('share.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('seen');
        },
    });
}

onMounted(() => {
    console.log(`share ${props.share.id} sent: ${isShareSent.value}`);
});

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

</script>

<template>
    <div 
        class="p-1 my-2 border-solid border-2 w-full flex flex-row justify-between"
        :class="isDebtOwner ? 'border-green-400' : ''"
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
                <form class="flex flex-col items-center p-1" @submit.prevent>
                    <small>Sent</small>
                    <label 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                        :class="sendShareForm.sent ? 'border-green-400' : 'border-red-400'"
                        :for="'sent-' + share.id"
                    >
                        <i class="fa-solid fa-check"></i>
                    </label>
                    <input 
                        type="checkbox" 
                        :id="'sent-' + share.id" 
                        class="hidden"
                        @click="sendShare"
                        v-model="sendShareForm.sent"
                    >
                </form>
            </div>
            <div>
                <form class="flex flex-col items-center p-1" @submit.prevent>
                    <small>Seen</small>
                    <label 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                        :class="seenShareForm.seen ? 'border-green-400' : 'border-red-400'"
                        :for="'seen-' + share.id"
                    >
                        <i class="fa-solid fa-check"></i>
                    </label>
                    <input 
                        type="checkbox" 
                        :id="'seen-' + share.id" 
                        class="hidden" 
                        @click="seenShare"
                        v-model="seenShareForm.seen"
                    >
                </form>
            </div>
        </div>

        <!-- <div 
            v-if="!isDebtOwner"    
            class="flex flex-row"
        >
            <form class="flex flex-col items-center p-1" @submit.prevent>
                <small>Sent</small>
                <label 
                    style="height:40px;width:40px;border-radius:50%" 
                    class="border-solid border-2 flex justify-center items-center"
                    :class="sendShareForm.sent ? 'border-green-400' : 'border-red-400'"
                    for="sent"
                >
                    <i class="fa-solid fa-check"></i>
                </label>
                <input 
                    type="checkbox" 
                    id="sent" 
                    class="hidden" 
                    @click="sendShare"
                    v-model="sendShareForm.sent"
                >
            </form>
            <form class="flex flex-col items-center p-1">
                <small>Seen</small>
                <label 
                    style="height:40px;width:40px;border-radius:50%" 
                    class="border-solid border-2 flex justify-center items-center"
                    for="seen"                
                >
                    <i class="fa-solid fa-check"></i>
                </label>
                <input 
                    type="checkbox" 
                    id="sent" 
                    class="hidden" 
                    value="seen"
                    @click="seenShare"
                    v-model="isShareSeen"
                >
            </form>
        </div> -->
            
    
    </div>
</template>

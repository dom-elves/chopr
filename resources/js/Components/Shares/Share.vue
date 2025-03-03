<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { currencies } from '@/currencies.js';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    share: {
        type: Object,
    },
    currency: {
        type: String,
    },
    debtOwner: {
        type: Number,
    }
});

const isDebtOwner = ref(props.debtOwner === props.share.group_user_id);
let isShareSent = ref(props.share.paid_amount === props.share.amount);
let isShareSeen = ref(props.share.cleared);
const shareId = props.share.id;

const sendShareForm = useForm({
    share_id: props.share.id,
    sent: isShareSent.value,
    group_user_id: props.share.group_user_id,
});

function sendShare() {
    console.log(sendShareForm.sent);
    router.patch(route('share.update', sendShareForm));
}

// let seenShareForm = useForm({
//     share_id: props.share.id,
//     seen: isShareSeen.value,
//     group_user_id: props.share.group_user_id,
// });

// function seenShare() {
//     console.log(sendShareForm);
//     router.patch(route('share.update', seenShareForm));
// }

onMounted(() => {
    // console.log('id on load', props.share.id);
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
                {{ share.group_user.user.name }}
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
        </div>
            
    
    </div>
</template>

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
});

let isSharePaid = ref(props.share.paid_amount === props.share.amount);
let isShareCleared = ref(props.share.cleared);

const shareId = props.share.id;

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

const form = useForm({
    share_id: props.share.id,
    sent: isSharePaid.value,
    seen: props.share.cleared,
    group_user_id: props.share.group_user_id,
});

onMounted(() => {});

function updateShare() {
    // console.log('posting', form);
    // console.log('but it is actually', props.share.id);
    // form.patch(route('share.update'), {
    //     onError: (error) => {
    //         console.log(error);
    //         // formErrors.name = error.name;
    //         // formErrors.amount = error.amount;
    //         // formErrors.group_user_values = error.group_user_values;
    //         // formErrors.currency = error.currency;
    //     },
    // })
    router.patch(route('share.update', {
        share_id: shareId,
    }));
}
</script>

<template>
    <div 
        class="p-1 my-2 border-solid border-2 w-full flex flex-row justify-between"
    >
        <div 
            class="flex-coln flex-start" 
   
            style="height:70px"
        >
            <p>{{ share.group_user.user.name }}</p>
            <p>{{ debtCurrency.symbol }}{{ share.amount }}</p>
        </div>
            <div class="flex flex-row">
                <form class="flex flex-col items-center p-1">
                    <input
                        v-model="props.share.id"
                        type="number"
                        id="share"
                        value="share_id"
                        class="hidden"
                    >
                    <small>Sent</small>
                    <label 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                        
                        for="sent"
                    >
                        <i class="fa-solid fa-check"></i>
                    </label>
                    <input 
                        type="checkbox" 
                        id="sent" 
                        class="hidden" 
                        value="sent"
                        @change="updateShare"
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
                        
                    >
                </form>
            </div>
            
    
    </div>
</template>

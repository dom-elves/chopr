<script setup>
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import { currencies } from '@/currencies.js';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    share: {
        type: Object,
    },
    currency: {
        type: String,
    },
});

let isShareCleared = ref(props.share.cleared);

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

function clearShare() {
    isShareCleared.value = isShareCleared.value ? false : true;
    router.post(route('share.update', { 
        'share_id': props.share.id,
        'new_status': isShareCleared.value,
        'paid_amount': props.share.amount,
    }));
}

// updateShare will be a different method, specifically for updating the amount 
// rather than just cleared/not cleared

// todo: also need to make it so clearShare() is only permitted if you are the debt owner
</script>

<template>
    <div 
        class="p-1 my-2 border-solid border-2 w-full flex flex-row justify-between"
        :class="share.cleared ? 'border-emerald-600' : 'border-red-600'"
    >
        <div 
            class="flex-coln flex-start" 
            @click="clearShare()"
            style="height:70px"
        >
            <p>{{ share.group_user.user.name }}</p>
            <p>{{ debtCurrency.symbol }}{{ share.amount }}</p>
        </div>
       
            <div class="flex flex-row">
                <div class="flex flex-col items-center p-1">
                    <small>Payer</small>
                    <div 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                    >
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
                <div class="flex flex-col items-center p-1">
                    <small>Payee</small>
                    <div 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                    >
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
            </div>
            
    
    </div>
</template>

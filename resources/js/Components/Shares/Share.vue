<script setup>
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
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

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

onMounted(() => console.log(isSharePaid.value));

const form = useForm({
    share_id: props.share.id,
    sent: isSharePaid,
    seen: props.share.cleared,
});

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
            <form class="flex flex-row">
                <div class="flex flex-col items-center p-1">
                    <small>Sent</small>
                    <label 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                        :class="isSharePaid ? 'border-amber-600' : 'border-red-600'"
                        for="sent"
                        @change="router.patch(route('share.update', form))"
                    >
                        <i class="fa-solid fa-check"></i>
                    </label>
                    <input 
                        type="checkbox" 
                        id="sent" 
                        class="hidden" 
                        value="sent"
                        @change="router.patch(route('share.update', form))"
                    >
                </div>
                <div class="flex flex-col items-center p-1">
                    <small>Seen</small>
                    <label 
                        style="height:40px;width:40px;border-radius:50%" 
                        class="border-solid border-2 flex justify-center items-center"
                        :class="isShareCleared ? 'border-green-600' : 'border-red-600'"
                        for="seen"
                        @change="router.patch(route('share.update', form))"
                    >
                        <i class="fa-solid fa-check"></i>
                    </label>
                    <input type="checkbox" id="sent" class="hidden" value="seen">
                </div>
            </form>
            
    
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { currencies } from '@/currencies.js';

const props = defineProps({
    share: {
        type: Object,
    },
    currency: {
        type: String,
    },
});

// todo: figure out a way to stop having to use this function in multiple places
const debtCurrency = computed(() => {
    return currencies.find((currency) => currency.code === props.currency)
});

</script>

<template>
    <div 
        class="p-1 my-2 border-solid border-2 w-full" 
        :class="share.cleared ? 'border-emerald-600' : 'border-red-600'"
    >
        <p>{{ share.group_user.user.name }}</p>
        <p>{{ debtCurrency.symbol }}{{ share.amount }}</p>
    </div>
</template>

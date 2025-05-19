<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { store } from '@/store.js';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
    split_even: {
        type: Boolean,
    },
});

const share = ref({
    user_id: props.group_user.user_id,
    group_user_id: props.group_user.id,
    name: '',
    amount: 0,
});

function setShareName() {
    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).name = share.value.name;
}
function setShareAmount() {
    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).amount = share.value.amount;
}

onMounted(() => console.log(props.split_even));

</script>
<template>
    <div class="flex">
        <p>
            {{ group_user.user.name }}{{ group_user.user_id }}
        </p>
        <div>
            <label 
                :for="`share-name-${group_user.id}`"
            >
                Share name:
            </label>
            <input 
                type="text"
                :id="`share-name-${group_user.id}`"
                :name="`share-name-${group_user.id}`"
                v-model="share.name"
                @change="setShareName"
            >
        </div>
        <div v-if="!split_even">
            <label 
                :for="`share-amount-${group_user.id}`"
            >
                Amount
            </label>
            <input 
                type="number"
                step="0.01"
                :id="`share-amount-${group_user.id}`"
                :name="`share-amount-${group_user.id}`"
                v-model="share.amount"
                @change="setShareAmount"
                :disabled="split_even"
            >
        </div>
        <div v-else>
            <label
                :for="`share-amount-split-even${group_user.id}`"
                class="hidden"
            >
                Amount
            </label>
            <p>{{ share.amount }}</p>
            <input
                type="checkbox"
                :id="`share-amount-split-even${group_user.id}`"
            >
        </div>
    </div>
</template>
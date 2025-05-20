<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { store } from '@/store.js';
import Slider from '@/Components/Slider.vue';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
});

const share = ref({
    user_id: props.group_user.user_id,
    group_user_id: props.group_user.id,
    name: '',
    amount: 0,
    checked: false,
});

function setShareName() {
    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).name = share.value.name;

    // todo: maybe introduce default selection on naming a share when split even
    // not sure it makes sense from a user perspective, need to ask for opinions
}
function setShareAmount() {
    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).amount = share.value.amount;

    // because adding a number then removing it from the input defaults to '', rather than 0
    if (share.value.amount == '') {
        store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).amount = 0;
    }

    store.calcTotalAmount();
}

function toggleShareChecked(toggle) {
    share.value.checked = toggle;

    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).checked = share.value.checked;

    store.splitEven();
}

onMounted(() => store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).amount = share.value.amount);

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
        <div v-if="!store.addDebtForm.split_even">
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
                :disabled="store.addDebtForm.split_even"
            >
        </div>
        <div v-else>
            <!-- todo: put label prop in a sensible var -->
            <Slider
                :label="String(store.addDebtForm.user_shares.find((userShare) => userShare.user_id == share.user_id).amount)"
                @toggled="toggleShareChecked"
            >
            </Slider>
        </div>
    </div>
</template>
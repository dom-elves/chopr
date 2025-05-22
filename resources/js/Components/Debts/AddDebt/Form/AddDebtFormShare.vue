<script setup>
import { onMounted, ref } from 'vue';
import { store } from '@/store.js';
import Slider from '@/Components/Slider.vue';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
});

const share = ref(store.addDebtForm.user_shares.find((userShare) => 
    userShare.user_id == props.group_user.user_id
));

function setShareAmount() {
    // checked is toggled so that if the user switches to split even mid-debt creation
    // the added users are retained
    share.value.checked = true;
    // because adding a number then removing it from the input defaults to '', rather than 0
    if (share.value.amount == '') {
        share.value.checked = false;
        share.value.amount = 0;
    }

    store.calcTotalAmount();
}

function toggleShareChecked(toggle) {
    share.value.checked = toggle;

    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).checked = share.value.checked;

    store.splitEven();
}

onMounted(() => { console.log('mounted', share.value)});

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
            <Slider
                :label="String(share.amount)"
                @toggled="toggleShareChecked"
                :checked="share.checked"
            >
            </Slider>
        </div>
    </div>
</template>
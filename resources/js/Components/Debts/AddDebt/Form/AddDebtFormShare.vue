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
    <div class="flex justify-between items-center">
        <p class="w-1/3">
            {{ group_user.user.name }} {{ group_user.user_id }}
        </p>
        <div class="w1/3">
            <label 
                :for="`share-name-${group_user.id}`"
                class="hidden"
            >
                Share name:
            </label>
            <textarea
                type="text"
                :id="`share-name-${group_user.id}`"
                :name="`share-name-${group_user.id}`"
                v-model="share.name"
                placeholder="Share name..."
                maxlength="50"
                rows="2"
            >
            </textarea>
        </div>
        <div class="flex flex-row mx-2">
            <label 
                :for="`share-amount-${group_user.id}`"
                class="hidden"
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
            <div :class="store.addDebtForm.split_even ? '' : 'invisible'">
                <Slider
                    @toggled="toggleShareChecked"
                    :checked="share.checked"
                >
                </Slider>
            </div>
        </div>
    </div>
</template>
<style scoped>

textarea {
    resize: none;
}

textarea:invalid {
  border: 2px solid red;
}

</style>
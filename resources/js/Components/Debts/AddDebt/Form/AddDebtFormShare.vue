<script setup>
import { onMounted, ref, watch } from 'vue';
import { store } from '@/debt.js';
import Slider from '@/Components/Misc/Slider.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import Dinero from 'dinero.js'

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
});

const share = ref(store.addDebtForm.user_shares.find((userShare) => 
    userShare.user_id == props.group_user.user_id
));

// separate vars for major/minor units
const majorUnits = ref(0);
const minorUnits = ref(0);

function setShareAmount() {
    // if the user is interacting with either input
    // set it as checked
    share.value.checked = true;

    // removing 0s leads to input being empty string, to manually set to 0
    if (majorUnits.value == '') {
        majorUnits.value = 0;
    }

    // same as above
    if (minorUnits.value == '') {
        minorUnits.value = 0;
    }

    // non strict comparison to allow major and minor units being 0
    // uncheck if both are falsey
    if (majorUnits.value == 0 && minorUnits.value == 0) {
        share.value.checked = false;
        share.value.amount = 0;
    }

    // recalc with entire amount in minor units, e.g. £12.34 = 1234
    share.value.amount = majorUnits.value * 100 + minorUnits.value;
    store.calcTotalAmount();
}

function toggleShareChecked(toggle) {
    share.value.checked = toggle;

    store.addDebtForm.user_shares.find((userShare) => 
        userShare.user_id == share.value.user_id).checked = share.value.checked;

    store.splitEven();
}

onMounted(() => {

});

</script>
<template>
    <div class="flex flex-col py-1 mt-2 ">
        <p>
            {{ group_user.user.name }}
        </p>
        <div class="flex flex-row justify-around">
            <div class="flex flex-row items-center w-full mr-2">
                <label 
                    :for="`share-name-${group_user.id}`"
                    class="hidden"
                >
                    Share name:
                </label>
                <TextInput
                    type="text"
                    :id="`share-name-${group_user.id}`"
                    :name="`share-name-${group_user.id}`"
                    v-model="share.name"
                    placeholder="Share name..."
                    class="w-full"
                >
                </TextInput>
            </div>
            <div class="flex flex-row justify-center items-center">
                <label 
                    :for="`share-major-amount-${group_user.id}`"
                    class="hidden"
                >
                    Amount
                </label>
                <input 
                    type="number"
                    :id="`share-major-amount-${group_user.id}`"
                    :name="`share-major-amount-${group_user.id}`"
                    v-model="majorUnits"
                    @change="setShareAmount"
                    :disabled="store.addDebtForm.split_even"
                    class="w-1/2 md:w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mr-2"
                >
                <label 
                    :for="`share-minor-amount-${group_user.id}`"
                    class="hidden"
                >
                    Amount
                </label>
                <input 
                    type="number"
                    :id="`share-minor-amount-${group_user.id}`"
                    :name="`share-minor-amount-${group_user.id}`"
                    v-model="minorUnits"
                    @change="setShareAmount"
                    :disabled="store.addDebtForm.split_even"
                    class="w-1/2 md:w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mr-2"
                    maxlength="2"
                >
                <div :class="store.addDebtForm.split_even ? '' : 'invisible'">
                <Slider
                    @toggled="toggleShareChecked"
                    :checked="share.checked"
                    alignment="end"
                >
                </Slider>
                </div>
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
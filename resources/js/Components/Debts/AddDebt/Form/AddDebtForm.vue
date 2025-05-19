<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { store } from '@/store.js';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/InputError.vue';
import Slider from '@/Components/Slider.vue';
import AddDebtFormShare from './AddDebtFormShare.vue';
import AddDebtFormName from './AddDebtFormName.vue';
import AddDebtFormAmount from './AddDebtFormAmount.vue';

const props = defineProps({
    groups: {
        type: Object,
    }
});

// vars
// groups set as a variable so they can be filtered
// selected group is done by a dropdown
const groups = ref(props.groups);
const selectedGroup = ref(null);

// hopefully key doesn't break anything
// as it *should* only be used as a hack to refresh shares on split even toggle
const shareKey = ref(0);

// the form, taken from store
// set this on form submit
// unless submission can also be done in store.js 
const addDebtForm = useForm(store.addDebtForm);

/**
 * As the GroupPicker and CurrencySelector are dumb
 * so logic to set form values lives here
 * 
 * Slider is too simple to warrant it's own component
 */

function setSelectedGroup(groupId) {
    selectedGroup.value = groups.value.find((group) => group.id == groupId);
    store.addDebtForm.group_id = selectedGroup.value.id;

    const userShares = selectedGroup.value.group_users.map(group_user => ({
        user_id: group_user.user_id,
        name: '',
        amount: null,
    }));

    store.addDebtForm.user_shares = userShares;
}

function setSelectedCurrency(currency) {
    // currently everything sets to GBP so in the future, total balance can be sorted
    // eventually might have to add an entire new table for balances per user?
    // store.addDebtForm.currency = currency.code;
    store.addDebtForm.currency = 'GBP';
}

// toggling split even
function toggleSplitEven(toggle) {
    store.addDebtForm.split_even = toggle;
    addDebtForm.reset('user_ids', 'amount', 'user_shares');
    shareKey.value++
}

// user shares
function updateUserShares(submittedShare) {
    // // first, grab the share if it already exists
    // const existingShare = addDebtForm.user_shares.find((share) => share.user_id == submittedShare.user_id);
    // // if the share doesn't exist, simply add it to the array
    // // this is the most likely operation, so it goes first
    // if (!existingShare) {
    //     addDebtForm.user_shares.push(submittedShare);
    // } else {
    //     // otherwise, update the share with new info
    //     Object.assign(existingShare, submittedShare);
    // }
    // // if that new info was the share amount being set to 0/'', it's implied
    // // that the user wants the share removed, so filter & reassign
    // const filtered = addDebtForm.user_shares.filter((share) => share.amount != 0 || '');
    // addDebtForm.user_shares = filtered;
    // console.log(addDebtForm.user_shares);
}

onMounted(() => {
    store.addDebtForm.user_id = usePage().props.auth.user.id;
});

</script>
<template>
    <div>
        <form>
            <GroupPicker
                :groups="groups"
                :errors="addDebtForm.errors.group_id"
                @groupSelected="setSelectedGroup"
            >
            </GroupPicker>
            <AddDebtFormName
                :errors="addDebtForm.errors.group_id"
            >
            </AddDebtFormName>
            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="setSelectedCurrency"
            >
            </CurrencyPicker>
            <div v-if="selectedGroup">
                <div v-for="group_user in selectedGroup.group_users">
                    <AddDebtFormShare
                        :key="shareKey + group_user.id"
                        :group_user="group_user"
                        :split_even="addDebtForm.split_even"
                        @updateShare="updateUserShares"
                    >
                    </AddDebtFormShare>
                </div>
            </div>
            <!-- 
                split even just sends a signal, doesn't really need it's own component 
                name only has one because it can have errors, split even is binary,
                plus the shareKey++ hack won't work otherwise
             -->
            <Slider
                label="Split even?"
                @toggled="toggleSplitEven"
            >
            </Slider>
            <!-- two instances of this required -->
            <AddDebtFormAmount
                v-if="!addDebtForm.split_even"
                :errors="addDebtForm.errors.currency"
                :split_even="false"
            >
            </AddDebtFormAmount> 
            <AddDebtFormAmount
                v-if="addDebtForm.split_even"
                :errors="addDebtForm.errors.currency"
                :split_even="true"
            >
            </AddDebtFormAmount>
        </form>
    </div>
</template>
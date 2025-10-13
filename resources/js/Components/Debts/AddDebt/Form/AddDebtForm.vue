<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { store } from '@/debt.js';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import UserPicker from '@/Components/UserPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/InputError.vue';
import Slider from '@/Components/Slider.vue';
import AddDebtFormShare from './AddDebtFormShare.vue';
import AddDebtFormName from './AddDebtFormName.vue';
import AddDebtFormAmount from './AddDebtFormAmount.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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

// the form, taken from store
// set this on form submit
// unless submission can also be done in debt.js 
const addDebtForm = useForm({
        user_id: store.addDebtForm.user_id,
        group_id: store.addDebtForm.group_id,
        name: store.addDebtForm.name,
        currency: store.addDebtForm.currency,
        user_shares: store.addDebtForm.user_shares, 
        split_even: store.addDebtForm.split_even,
        amount: store.addDebtForm.amount,
    });

const shareKey = ref(0);

/**
 * As the GroupPicker and CurrencySelector are dumb
 * so logic to set form values lives here
 * 
 * Slider is too simple to warrant it's own component
 * Split even resets submitted data
 */

function setSelectedGroup(groupId) {
    selectedGroup.value = groups.value.find((group) => group.id == groupId);
    store.addDebtForm.group_id = selectedGroup.value.id;

    const userShares = selectedGroup.value.group_users.map(group_user => ({
        user_id: group_user.user_id,
        name: '',
        amount: 0,
    }));

    // reset the form amount to 0
    store.addDebtForm.amount = 0;
    // set the selected user to whoever is logged in by default
    // user can easily just change this
    store.addDebtForm.user_id = usePage().props.auth.user.id;
    // set the user shares to that of the newly selected group
    // refresh share component key so 'old' share values are removed
    // this is because user_shares can have a different format between groups (different ids)
    // but that amount is just a number
    store.addDebtForm.user_shares = userShares;
    shareKey.value++;
}

/**
 * Currently everything sets to GBP so in the future, total balance can be sorted by curency
 * Might have to add a new table for balances per user
 */
function setSelectedCurrency(currency) {
    // store.addDebtForm.currency = currency.code;
    store.addDebtForm.currency = 'GBP';
}

/**
 * Toggles between the debt being split even/custom shares. Basically looks at total_amount if split, 
 * and then indiviudal fields if not. 
 */
function toggleSplitEven(toggle) {
    store.addDebtForm.split_even = toggle;

    if (store.addDebtForm.split_even) {
        store.splitEven();
    } else {
        store.calcTotalAmount()
    }
}

/**
 * Sets someone to 'own' the debt. This plays into how total balances are created.
 * 
 */
function setDebtOwner(userId) {
    store.addDebtForm.user_id = userId;
}

/**
 * Set the id of the user building the form
 */
onMounted(() => {
    
});

watch(
    store.addDebtForm, (updated) => {
        addDebtForm.user_id = updated.user_id;
        addDebtForm.group_id = updated.group_id;
        addDebtForm.name = updated.name;
        addDebtForm.currency = updated.currency;
        addDebtForm.user_shares = updated.user_shares;
        addDebtForm.split_even = updated.split_even;
        addDebtForm.amount = updated.amount;
    }, { 
    deep: true 
});

function addDebt() {
    // remove all shares that are 0
    // empty string issue is sorted in the share component
    const filtered = store.addDebtForm.user_shares.filter((share) => share.amount != 0);
    addDebtForm.user_shares = filtered;
    
    addDebtForm.post(route('debt.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // retain the selected group
            setSelectedGroup(store.addDebtForm.group_id);
            // reset the name
            store.addDebtForm.name = '';
        },
        onError: (error) => {
            console.log('error', error);
        },
    })
}

</script>
<template>
    <div class="p-6 flex flex-col">
        <form @submit.prevent="addDebt">
            <h2 class="text-lg font-medium text-gray-900 text-center sm:text-left">
                Add a new Debt
            </h2>
            <GroupPicker
                :groups="groups"
                :errors="addDebtForm.errors.group_id"
                @groupSelected="setSelectedGroup"
            >
            </GroupPicker>
            <AddDebtFormName
                :errors="addDebtForm.errors.name"
            >
            </AddDebtFormName>
            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="setSelectedCurrency"
            >
            </CurrencyPicker>
            <div v-if="selectedGroup">
                <UserPicker
                    :group_users="selectedGroup.group_users"
                    :errors="addDebtForm.errors.user_id"
                    @userSelected="setDebtOwner"
                >
                </UserPicker>
                <div v-for="group_user in selectedGroup.group_users">
                    <AddDebtFormShare
                        :key="`${shareKey} + ${group_user.id}`"
                        :group_user="group_user"
                        :errors="addDebtForm.errors.user_shares"
                    >
                    </AddDebtFormShare>
                </div>
                <InputError class="mt-2" :message="addDebtForm.errors.user_shares" />
            </div> 
            <div class="flex flex-row mt-2 items-center justify-between">
                <div class="flex flex-col">
                    <p>Amount:</p>
                    <AddDebtFormAmount
                        :errors="addDebtForm.errors.amount"
                    >
                    </AddDebtFormAmount>
                </div>
                <Slider
                    label="Split even?"
                    alignment="right"
                    size="xs"
                    @toggled="toggleSplitEven"
                >
                </Slider>
            </div>
            <div class="flex flex-row mt-4 justify-center sm:justify-end">
                <SecondaryButton 
                    @click="creatingGroup = false"
                    type="button"
                >
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                >
                    Save
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
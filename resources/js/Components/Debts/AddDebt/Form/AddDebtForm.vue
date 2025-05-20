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

// the form, taken from store
// set this on form submit
// unless submission can also be done in store.js 
const addDebtForm = useForm(store.addDebtForm);

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
        amount: null,
    }));

    store.addDebtForm.user_shares = userShares;
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
 * Setting split even is to be a one way operation.
 * Upon switchin back from split even, just reset the amount & share values to 0.
 * Became far too confusing to retain values, recalculate without having a bunch of messy logic scatterered about.
 * 
 * todo: it currently leaves the amounts in the input field
 * but as soon as you start updating them, it works correctly
 * need to recalc total on switch back to regular form
 */
function toggleSplitEven(toggle) {
    store.addDebtForm.split_even = toggle;

    if (store.addDebtForm.split_even) {
        store.splitEven();
    } else {
        store.addDebtForm.amount = 0;
        store.addDebtForm.user_shares.forEach((userShare) => {
            userShare.amount = 0;
        });   
    }
}


onMounted(() => {
    store.addDebtForm.user_id = usePage().props.auth.user.id;
});

function addDebt() {
    console.log('stat', store.addDebtForm);
    store.addDebtForm.user_shares = store.addDebtForm.user_shares.filter((share) => share.amount != 0);
   
    console.log(store.addDebtForm.user_shares);
}

</script>
<template>
    <div>
        <form @submit.prevent="addDebt">
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
                        :group_user="group_user"
                    >
                    </AddDebtFormShare>
                </div>
            </div>
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
            <button class="bg-blue-400 text-white py-2 w-full" type="submit">Save</button>
        </form>
    </div>
</template>
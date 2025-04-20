<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import { currencies } from '@/currencies.js';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    groupUsers: {
        type: Object,
    },
    groupId: {
        type: Number,
    },
});

const isSplitEven = ref(false);
const splitEvenShareAmont = ref(0);


/**
 * The idea is to have two forms, one for custom shares and one for split even.
 * The endpoints & backend treatment remain the same though this seems like the easiest
 * frontend solution to get working, and can be refactored later. 
 */

/**
 * Custom shares
 */
const addDebtForm = useForm({
    group_id: props.groupId, 
    name: null,
    amount: 0,
    user_ids: {},
    split_even: false,
    currency: '',
});

function addDebt() {
    // filter out entires that are 0
    // prevents shares for 0 money being added
    const filtered = Object.fromEntries(
        Object.entries(addDebtForm.user_ids).filter(([key, value]) => value !== 0)
    );

    addDebtForm.user_ids = filtered;

    addDebtForm.post(route('debt.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // to avoid any confusion, reset everything on success 
            addDebtForm.reset();
            addDebtFormSplitEven.reset();
            selectedUsers.value = {};
        },
        onError: (error) => {

        },
    })
}

function updateCurrency(currency) {
    addDebtForm.currency = 'GBP';
    // addDebtForm.currency = currency;
}

// update the share for the user
// pass in input value & key from loop to get correct input change
// then add together the total values of the user_ids obj
function updateShare() {
    addDebtForm.amount = Object.values(addDebtForm.user_ids)
        .reduce((acc, value) => acc + value, 0);
}

/**
 * Split even
 * 
 * 1. Can likely be refactored
 * 2. There might be a better way to handle decimilisation
 * 3. There's probably a better way of handling the selected users & assigning share values,
 * as currently it's done via checkboxes and then manually assigning the values on amount change
 * whereas in the custom form, the user entered share input just is the value
 * 
 */
const addDebtFormSplitEven = useForm({
    group_id: props.groupId, 
    name: null,
    amount: 0,
    user_ids: {},
    split_even: true,
    currency: '',
});

// handling split even data in separate object to then be posted later
// as it got confusing with the checkboxes and binding values
const selectedUsers = reactive({});
const splitEvenShare = ref(0);

function addDebtSplitEven() {
    // first thing we do is assign the selected users & values to the form 
    addDebtFormSplitEven.user_ids = selectedUsers.value;

    addDebtFormSplitEven.post(route('debt.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // to avoid any confusion, reset everything on success 
            addDebtForm.reset();
            addDebtFormSplitEven.reset();
            selectedUsers.value = {};
        },
        onError: (error) => {

        },
    })
}

function updateCurrencySplitEven(currency) {
    addDebtFormSplitEven.currency = 'GBP';
    // addDebtFormSplitEven.currency = currency;
}

// this runs on user selection & total amount entry/change
function splitEven() {
    selectedUsers.value = Object.entries(addDebtFormSplitEven.user_ids)
        // filter adds kv pair to an array
        .filter(([key, value]) => value === true)
        // reduce 'pops' them out of the array, into the object
        .reduce((acc, [key, value]) => {
            acc[key] = value
            return acc
        }, {});

    // total users being added 
    const totalSelectedUsers = Object.keys(selectedUsers.value).length;
    // rounded share to 2 dp
    splitEvenShare.value = Math.floor((addDebtFormSplitEven.amount / totalSelectedUsers) * 100) / 100;
    
    // updating the object status from 'true' (selected) to the share amount
    selectedUsers.value = Object.fromEntries(
        Object.keys(selectedUsers.value).map(key => [key, splitEvenShare.value])
    );
    
    // remainder of what's lost when rounding to 2 dp
    const remainder = ((addDebtFormSplitEven.amount - (splitEvenShare.value * totalSelectedUsers))).toFixed(2);
    // first user in the object is unlucky, gets given the remainder (a matter pennies)
    const first = Object.keys(selectedUsers.value)[0];
    selectedUsers.value[first] = (splitEvenShare.value + Number(remainder));
}

</script>

<template>
    <div class="py-4 px-2 my-2 border-solid border-2 border-green-600 bg-white flex flex-column">

        <!-- toggles which form is visible to the user -->
        <div>
            <p>Split even?</p>
            <label class="switch">
                <input 
                    type="checkbox"
                    @change="isSplitEven = !isSplitEven"
                >
                <span class="slider round"></span>
            </label>
        </div>
        
        <!-- custom share form -->
        <form @submit.prevent="addDebt" v-if="!isSplitEven">
            <!--  debt name -->
            <div class="my-2">
                <label 
                    for="debt-name" 
                    class="block text-sm font-medium text-gray-700 hidden"
                    id="debtName"
                >
                    Debt Name
                </label>
                <input
                    v-model="addDebtForm.name" 
                    type="text" 
                    id="debt-name" 
                    name="debt-name" 
                    class="w-full"
                    placeholder="Debt Name"
                    aria-labelledby="debtName"
                    @input="addDebtFormSplitEven.name = addDebtForm.name"
                />
                <InputError class="mt-2" :message="addDebtForm.errors.name" />
            </div>
            <!-- currency picker -->
            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="updateCurrency"
            >
            </CurrencyPicker>
            <!-- users -->
            <div v-for="group_user in props.groupUsers"
                class="flex flex-row justify-between items-center" 
                style="height:70px"
            >
                <label :for="group_user.id">
                    {{ group_user.user.name }}
                </label>
                <input
                    type="number"
                    step="0.01"
                    class="w-1/4"
                    :id="group_user.user_id"
                    :name="`group_user-${group_user.id}`"
                    v-model="addDebtForm.user_ids[group_user.user_id]"
                    @change="updateShare" 
                >
            </div>
            <InputError class="mt-2" :message="addDebtForm.errors.user_ids" />
            <!-- total amount -->
            <div 
                class="flex flex-row justify-between items-center" 
                style="height:70px"
            >
                <label for="amount">
                    Total:
                </label>
                <input
                    type="number"
                    step="0.01"
                    class="w-1/4"
                    id="amount"
                    name="amount"
                    v-model="addDebtForm.amount"
                    :disabled="!isSplitEven" 
                >
            </div>
            <InputError class="mt-2" :message="addDebtForm.errors.amount" />
            <button class="bg-blue-400 text-white p-2 w-full" type="submit">Save</button>
        </form>

        <!-- split even form -->
        <form @submit.prevent="addDebtSplitEven" v-if="isSplitEven">
            <!-- debt name -->
            <div class="my-2">
                <label 
                    for="debt-name" 
                    class="block text-sm font-medium text-gray-700 hidden"
                    id="debtNameSplitEven"
                >
                    Debt Name
                </label>
                <input
                    v-model="addDebtFormSplitEven.name" 
                    type="text" 
                    id="debt-name" 
                    name="debt-name" 
                    class="w-full"
                    placeholder="Debt Name"
                    aria-labelledby="debtNameSplitEven"
                    @input="addDebtForm.name = addDebtFormSplitEven.name"
                />
                <InputError class="mt-2" :message="addDebtFormSplitEven.errors.name" />
            </div>
            <!-- currency picker -->
            <CurrencyPicker
                :errors="addDebtFormSplitEven.errors.currency"
                @currencySelected="updateCurrencySplitEven"
            >
            </CurrencyPicker>
            <!-- users -->
            <div v-for="group_user in props.groupUsers"
                class="flex flex-row justify-between items-center" 
                style="height:70px"
            >
                <label :for="group_user.id">
                    {{ group_user.user.name }}
                </label>
                <p v-if="addDebtFormSplitEven.user_ids[group_user.user_id]">
                    {{ splitEvenShare }}
                </p>
                <input
                    type="checkbox"
                    :id="`${group_user.user_id}-split_even-selected`"
                    @change="splitEven"
                    v-model="addDebtFormSplitEven.user_ids[group_user.user_id]"
                >
            </div>
            <InputError class="mt-2" :message="addDebtFormSplitEven.errors.user_ids" />
            <!-- total amount -->
            <div 
                class="flex flex-row justify-between items-center" 
                style="height:70px"
            >
                <label for="amount">
                    Total:
                </label>
                <input
                    type="number"
                    step="0.01"
                    class="w-1/4"
                    id="amount"
                    name="amount"
                    v-model="addDebtFormSplitEven.amount"
                    @change="splitEven"
                >
            </div>
            <InputError class="mt-2" :message="addDebtFormSplitEven.errors.amount" />
            <button class="bg-blue-400 text-white p-2 w-full" type="submit">Save</button>
        </form>
    </div>
</template>
<style>

/* todo: make this into a component */

/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

/* for the split_even share inputs */
*:disabled {
  background-color: rgb(230, 225, 225);
  opacity: 1;
}
</style>
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
            // todo: add a success message/toast
            addDebtForm.reset();
        },
        onError: (error) => {
            console.log(addDebtForm.errors);
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
function updateShare(userId, shareValue) {
    addDebtForm.user_ids[userId] = shareValue;
    addDebtForm.amount = Object.values(addDebtForm.user_ids)
        .reduce((acc, value) => acc + value, 0);
}

/**
 * Split even
 */
const addDebtFormSplitEven = useForm({
    group_id: props.groupId, 
    name: null,
    amount: 0,
    user_ids: {},
    split_even: true,
    currency: '',
});

function addDebtSplitEven() {
    
    addDebtFormSplitEven.post(route('debt.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // todo: add a success message/toast
            addDebtFormSplitEven.reset();
        },
        onError: (error) => {
            console.log(addDebtFormSplitEven.errors);
        },
    })
}

function updateCurrencySplitEven(currency) {
    addDebtFormSplitEven.currency = 'GBP';
    // addDebtForm.currency = currency;
}


// todo: fix/change this or put it somewhere else
function splitEven() {
    const share = Number(addDebtFormSplitEven.amount / props.groupUsers.length);
    props.groupUsers.forEach((group_user) => {
        addDebtFormSplitEven.user_ids[group_user.id] = share;
    });
}



// // for showing the 'amount' input as you can't bind to values to a checkbox
// watch(() => addDebtForm.split_even, () => {
//     isSplitEven = addDebtForm.split_even;
// });

/**
 * TODO:
 * For split even, just add two forms; one for a 'custom' shares debt and one for a 'split even'
 * The idea would be to use the same methods, data structure etc but a lot of
 * enabling/disabling inputs just seems messy and annoying and because
 * everything is done on change/blur to appear nice and seamless and automatic
 * two forms would just be easier to manage & read
 */
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
                    @change="updateShare(group_user.user_id, Number($event.target.value))" 
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
            <!-- hidden input to submit the debt being split even -->
            <input
                type="hidden"
                v-model="addDebtFormSplitEven.split_even"
                name="split_even"
            />
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
                    v-model="addDebtFormSplitEven.name" 
                    type="text" 
                    id="debt-name" 
                    name="debt-name" 
                    class="w-full"
                    placeholder="Debt Name"
                    aria-labelledby="debtName"
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
                <input
                    type="number"
                    step="0.01"
                    class="w-1/4"
                    :id="`${group_user.user_id}-split_even`"
                    :name="`group_user-${group_user.id}-split_even`"
                    v-model="addDebtFormSplitEven.user_ids[group_user.user_id]"
                    disabled
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


        <!-- <form @submit.prevent="addDebt">

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
                />
                <InputError class="mt-2" :message="addDebtForm.errors.name" />
            </div>

            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="updateCurrency"
            >
            </CurrencyPicker>

            <div class="flex flex-row">
                <div>
                    <label for="split-even">Split even?</label>
                    <input
                        v-model="addDebtForm.split_even" 
                        type="checkbox" 
                        name="split-even" 
                        id="split-even"
                        @change="splitEven"
                    />
                </div>
                <div v-show="isSplitEven">
                    <label for="debt-amount">Amount:</label>
                    <input
                        v-model="addDebtForm.amount"
                        type="number"
                        id="debt-amount"
                        name="debt-amount"
                        @change="splitEven"
                    />
                </div>
            </div>
         
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
                    @change="updateShare(group_user.user_id, Number($event.target.value))" 
                    :disabled="isSplitEven"
                >
            </div>
            <InputError class="mt-2" :message="addDebtForm.errors.user_ids" />
          
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
        </form> -->
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
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

onMounted(() => {

});

let isSplitEven = ref(false);

// form data itself
const addDebtForm = useForm({
    group_id: props.groupId, 
    name: null,
    amount: 0,
    user_ids: {},
    split_even: false,
    currency: '',
});

// methods
// post debt to backend
function addDebt() {
    // filter out entires that are 0
    // prevents shares for 0 money being added
    const filtered = Object.fromEntries(
        Object.entries(addDebtForm.user_ids).filter(([key, value]) => value !== 0)
    );

    addDebtForm.user_ids = filtered;

    addDebtForm.post(route('debt.store'),{
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

// update the share for the user
// pass in input value & key from loop to get correct input change
// then add together the total values of the user_ids obj
function updateShare(userId, shareValue) {
    addDebtForm.user_ids[userId] = shareValue;
    addDebtForm.amount = Object.values(addDebtForm.user_ids)
        .reduce((acc, value) => acc + value, 0);
}

// from the CurrencyPicker child component
function updateCurrency(currency) {
    addDebtForm.currency = currency;
}

// todo: fix/change this or put it somewhere else
function splitEven() {
    const share = Number(addDebtForm.amount / props.groupUsers.length);
    props.groupUsers.forEach((group_user) => {
        addDebtForm.user_ids[group_user.id] = share;
    });
}

// for showing the 'amount' input as you can't bind to values to a checkbox
watch(() => addDebtForm.split_even, () => {
    isSplitEven = addDebtForm.split_even;
});
</script>

<template>
    <div class="py-4 px-2 my-2 border-solid border-2 border-green-600 bg-white flex flex-column">
        <form @submit.prevent="addDebt">
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
            <!-- <div class="flex flex-row">
                <div>
                    <label for="split-even">Split even?</label>
                    <input
                        v-model="addDebtForm.split_even" 
                        type="checkbox" 
                        name="split-even" 
                        id="split-even"
            
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
            </div> -->
            <div v-for="group_user in props.groupUsers"
                class="flex flex-row justify-between items-center" 
                style="height:70px"
            >
            <!-- possibly add errors here but I can't thing of anything outside of step -->
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
                    disabled 
                >
            </div>
            <InputError class="mt-2" :message="addDebtForm.errors.amount" />
            <button class="bg-blue-400 text-white p-2 w-full" type="submit">Save</button>
        </form>
    </div>
</template>

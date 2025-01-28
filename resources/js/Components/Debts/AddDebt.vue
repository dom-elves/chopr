<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AddShare from '@/Components/Shares/AddShare.vue';
import { currencies } from '@/currencies.js';

// props
const props = defineProps({
    groupUsers: {
        type: Object,
    },
    groupId: {
        type: Number,
    },
});

// onMounted(() => {
//     console.log('mounted');
//     console.log(currencies);
// });

// form data itself
const formData = useForm({
    group_id: props.groupId, 
    name: null,
    amount: 0,
    group_user_values: {},
    split_even: false,
    currency: '',
});

// errors, some are handled by input by default
// will add the rest if necessary but this is fine to be getting on with
const formErrors = reactive({
    name: null,
    amount: null,
    group_user_valies: null,
    currency: null,
});

// computed properties
// automatically calced when entering shares manually
const debtTotalValue = computed(() => {
    return Object.values(formData.group_user_values).reduce((acc, value) => acc + value, 0);
});

// methods
// post debt to backend
function addDebt() {
    formData.amount = debtTotalValue;
    console.log('form', formData);
    formData.post(route('debt.store'), {
        onError: (error) => {
            console.log(error);
            formErrors.name = error.name;
            formErrors.amount = error.amount;
            formErrors.group_user_values = error.group_user_values;
            formErrors.currency = error.currency;
        },
    })

    // todo: add a callback that resets all inputs 
    // and also shows a 'debt added!' success message or something
}

// update share value based on signal from child component
function updateShare(groupUserId, shareValue) {
    formData.group_user_values[groupUserId] = shareValue;
}

// splits the debt evenly on checkbox
// resets to 0 on unchecked
// watch(() => formData.split_even, () => {
//     const amount = formData.amount / props.groupUsers.length;
//     for (const user in formData.group_users) {
//         if (formData.split_even) {
//             formData.group_users[user] = amount;
//         } else {
//             formData.group_users[user] = 0;
//         }
//     }
// });
</script>

<template>
    <div class="py-4 px-2 my-2 border-solid border-2 border-green-600 bg-white flex flex-column">
        <form @submit.prevent="addDebt">
            <div>
                <p v-if="formErrors.name" class="text-red-500">{{ formErrors.name }}</p>
                <label for="debt-name" class="block text-sm font-medium text-gray-700">Debt Name</label>
                <input
                    v-model="formData.name" 
                    type="text" 
                    id="debt-name" 
                    name="debt-name" 
                    class="border-solid border-2 border-gray-400"
                />
            </div>
            <div>
                <p v-if="formErrors.currency" class="text-red-500">{{ formErrors.currency }}</p>
                <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                <select v-model="formData.currency" id="currency">
                    <option v-for="currency in currencies"
                        :key="currency.code"
                        :value="currency.code"
                    >
                        {{  currency.name }}
                    </option>>
                </select>
            </div>
            <!-- <div class="flex flex-row">
                <div>
                    <label for="split-even">Split even?</label>
                    <input
                        v-model="formData.split_even" 
                        type="checkbox" 
                        name="split-even" 
                        id="split-even"
            
                    />
                </div>
                <div>
                    <label for="debt-amount">Amount:</label>
                    <input
                        
                        type="number"
                        id="debt-amount"
                        name="debt-amount"
                    />
                </div>
            </div> -->
           
            <AddShare
                v-for="group_user in props.groupUsers"
                :group-user="group_user"
                @emit-share="updateShare"
            >
            </AddShare>
           
            <p v-if="formErrors.group_user_values" class="text-red-500">{{ formErrors.group_user_values }}</p>
            <p v-if="formErrors.amount" class="text-red-500">{{ formErrors.amount }}</p>
            <p>Total: {{ debtTotalValue }}</p>
            <button class="bg-blue-400 text-white p-2" type="submit">Save</button>
        </form>
    </div>
</template>

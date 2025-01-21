<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AddShare from '@/Components/Shares/AddShare.vue';

// props
const props = defineProps({
    groupUsers: {
        type: Object,
    },
    groupId: {
        type: Number,
    },
});

// data
// toggle for showing the form
const showForm = ref(false);

// form data itself
const formData = useForm({
    group_id: props.groupId, 
    name: null,
    amount: 0,
    group_user_values: {},
    split_even: false,
});

// errors, some are handled by input by default
// will add the rest if necessary but this is fine to be getting on with
const formErrors = reactive({
    name: null,
    amount: null,
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
 
    formData.post(route('debt.store'), {
        onError: (error) => {
            console.log(error);
            formErrors.name = error.name;
            formErrors.amount = error.amount;
        },
    })
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
    <div class="py-4 m-2 border-solid border-2 border-green-600 bg-white">
        <button class="bg-blue-400 text-white p-2" @click="showForm = !showForm">Add a debt</button>
        <div v-show="showForm">
            <form @submit.prevent="addDebt">
                <p v-if="formErrors.name" class="text-red-500">{{ formErrors.name }}</p>
                <div>
                    <label for="debt-name">Debt Name</label>
                    <input
                        v-model="formData.name" 
                        type="text" 
                        id="debt-name" 
                        name="debt-name" 
                        class="p-2 m-2 border-solid border-2 border-gray-400"
                    />
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
                <div v-for="group_user in props.groupUsers">
                    <AddShare
                        :group-user="group_user"
                        @emit-share="updateShare"
                    >
                    </AddShare>
                </div>
                <p v-if="formErrors.amount" class="text-red-500">{{ formErrors.amount }}</p>
                <p>Total: {{ debtTotalValue }}</p>
                <button class="bg-blue-400 text-white p-2" type="submit">Save</button>
            </form>
        </div>
    </div>
</template>

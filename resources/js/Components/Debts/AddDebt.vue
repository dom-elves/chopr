<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';


const props = defineProps({
    groupUsers: {
        type: Object,
    },
});
// onMounted(() => console.log('hereee', props.groupUsers));
const showAddDebtForm = ref(false);

// generate form data object based on groupUsers passed in as a prop
// todo: see if there's a way to improve this
const addDebtForm = reactive({
    debtName: '',
    debtAmount: 0,
    selectedUsers: props.groupUsers.reduce((accumulator, user) => {
        accumulator[user.id] = 0;
        return accumulator;
    }, {}),
    splitEven: false,
});

function addDebt() {
    router.post(route('debt.store'), addDebtForm);
}

// splits the debt evenly on checkbox
// resets to 0 on unchecked
watch(() => addDebtForm.splitEven, () => {
    const amount = addDebtForm.debtAmount / props.groupUsers.length;
    for (const user in addDebtForm.selectedUsers) {
        if (addDebtForm.splitEven) {
            addDebtForm.selectedUsers[user] = amount;
        } else {
            addDebtForm.selectedUsers[user] = 0;
        }
    }
});
</script>

<template>
    <div class="py-4 m-2 border-solid border-2 border-green-600 bg-white">
        <button class="bg-blue-400 text-white p-2" @click="showAddDebtForm = !showAddDebtForm">Add a debt</button>
        <div v-show="showAddDebtForm">
            <form @submit.prevent="addDebt">
                <div>
                    <label for="debt-name">Debt Name</label>
                    <input
                        v-model="addDebtForm.debtName" 
                        type="text" 
                        id="debt-name" 
                        name="debt-name" 
                        class="p-2 m-2 border-solid border-2 border-gray-400"
                    />
                </div>
                <div>
                    <label for="debt-amount">Amount:</label>
                    <input
                        v-model="addDebtForm.debtAmount"
                        type="number"
                        id="debt-amount"
                        name="debt-amount"
                    />
                </div>
                <div>
                    <label for="split-even">Split even?</label>
                    <input
                        v-model="addDebtForm.splitEven" 
                        type="checkbox" 
                        name="split-even" 
                        id="split-even" />
                </div>
                <div v-for="group_user in props.groupUsers">
                    <label :for="group_user.id">{{ group_user.user.name }}</label>
                    <input
                        v-model="addDebtForm.selectedUsers[group_user.id]" 
                        type="number"
                        step="0.01" 
                        :id="group_user.id"
                        :disabled="addDebtForm.splitEven"
                        class="disabled:bg-slate-50"
                        @change="splitEven"
                    />
                </div>
                <button class="bg-blue-400 text-white p-2" type="submit">Save</button>
            </form>
        </div>
    </div>
</template>

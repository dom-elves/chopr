<script setup>
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';


const props = defineProps({
    groupUsers: {
        type: Object,
    },
});
// onMounted(() => console.log('hereee', props.groupUsers));
const showAddDebtForm = ref(false);

// generate form data object based on groupUsers passed in as a prop
const addDebtForm = reactive({
    debtName: '',
    selectedUsers: props.groupUsers.reduce((accumulator, user) => {
        console.log('this', accumulator, user);
        accumulator[user.id] = false;
        return accumulator;
    }, {})
});

function addDebt() {
//   router.post('/debts/save', form)
console.log('form', addDebtForm);
}
</script>

<template>
    <div class="py-4 m-2 border-solid border-2 border-green-600 bg-white">
        <button class="bg-blue-400 text-white p-2" @click="showAddDebtForm = !showAddDebtForm">Add a debt</button>
        <div v-show="showAddDebtForm">
            <form @submit.prevent="addDebt">
                <label for="debt-name">Debt Name</label>
                <input
                    v-model="addDebtForm.debtName" 
                    type="text" 
                    id="debt-name" 
                    name="debt-name" 
                    class="p-2 m-2 border-solid border-2 border-gray-400"
                />
                <div v-for="group_user in props.groupUsers">
                    <label :for="group_user.id">{{ group_user.user.name }}</label>
                    <input
                        v-model="addDebtForm.selectedUsers[group_user.id]" 
                        type="checkbox" 
                        :id="group_user.id"
                    />
                </div>
                <button class="bg-blue-400 text-white p-2" type="submit">Save</button>
            </form>
        </div>
    </div>
</template>
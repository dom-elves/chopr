<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
    group: {
        type: Object,
    }
});

const group_users = ref(props.group.group_users);
const debt_group_users = ref(props.debt.shares.map((share) => share.group_user));
const group_users_not_in_debt = group_users.value.filter(
        (user) => !debt_group_users.value.some((debt_user) => debt_user.id === user.id)
    );

const addShareForm = useForm({
    debt_id: props.debt.id,
    amount: 0,
    user_id: '',
});

function addShare() {
    addShareForm.post(route('share.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addShareForm.reset();
        },
        onError: (error) => {
            console.log(error);
        },
    });
}
// watch(debt_group_users, function addAddables());

onMounted(() => {

})

</script>

<template>
    <div>
        <form @submit.prevent="addShare">
            <select 
                @change="$emit('groupUsersUpdated', $event.target.value)"
                v-model="addShareForm.user_id"
            >
                <option value="" disabled selected>Select a user</option>
                <option 
                    v-for="group_user in group_users_not_in_debt" 
                    :key="group_user.id" 
                    :value="group_user.user.id"
                    
                >
                    {{ group_user.user.name }}
                </option>
            </select>
            <label for="amount">Amount</label>
            <input type="number" id="amount" v-model="addShareForm.amount" />
            <button type="submit">Add</button>
            <InputError v-for="error in addShareForm.errors" :message="error" />
        </form> 
    </div>
</template>

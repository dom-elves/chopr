<script setup>
import { onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
    group_users: {
        type: Array,
    },
});
</script>

<template>
    <div>
        <Form 
            :action="route('share.store')"
            method="post"
            #default="{ errors }"
            resetOnSuccess
            :transform="data => ({ 
                    ...data, 
                    debt_id: props.debt.id,
                })"
        >
            <select 
                name="user_id"
                id="user_id"
            >
                <option value="" disabled selected>Select a user</option>
                <option 
                    v-for="group_user in group_users" 
                    :key="group_user.id" 
                    :value="group_user.user.id"
                    
                >
                    {{ group_user.user.name }}
                </option>
            </select>
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" />
            <label for="name">Name</label>
            <input type="text" id="name" name="name" />
            <PrimaryButton type="submit">Add Share</PrimaryButton>
            <InputError class="mt-2" v-for="error in errors" :message="error" />
        </Form> 
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import BigButton from '@/Components/BigButton.vue';

const showAddShare = ref(false)
const props = defineProps({
    debt: {
        type: Object,
    },
    group_users: {
        type: Array,
    },
});

onMounted(() => {
    
})
</script>

<template>
    <div>
        <BigButton v-if="!showAddShare" @click="showAddShare = !showAddShare">Add Share</BigButton>
        <Form
            v-if="showAddShare" 
            :action="route('share.store')"
            method="post"
            #default="{ errors }"
            resetOnSuccess
            :transform="data => ({ 
                ...data, 
                debt_id: props.debt.id,
            })"
            class="mt-4"
        >
            <select 
                name="user_id"
                id="user_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
            <label for="amount" class="hidden">Amount</label>
            <TextInput 
                type="number" 
                id="amount" 
                name="amount" 
                class="w-full mt-2"
                placeholder="Enter an amount"
            />
            <label for="name" class="hidden">Name</label>
            <TextInput 
                type="text" 
                id="name" 
                name="name" 
                class="w-full mt-2"
                placeholder="Enter a share name"
            />
            <div class="flex flex-row mt-2 w-full sm:justify-end">
                <SecondaryButton
                    type="button"
                    class="mr-2"
                    @click="showAddShare = !showAddShare"
                >
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                >
                    Save
                </PrimaryButton>
            </div>
            <InputError class="mt-2" v-for="error in errors" :message="error" />
        </Form> 
    </div>
</template>

<script setup>
import { onMounted, ref, inject, computed } from 'vue';
import InputError from '@/Components/Forms/InputError.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import BigButton from '@/Components/Misc/BigButton.vue';

const showAddShare = ref(false)
const props = defineProps({
    debt: {
        type: Object,
    },
    group_users: {
        type: Array,
    },
});

const refresh = inject('collapsibleRefresh');

/**
 * Initially get the group users that have shares for the debt,
 * then compare that to users in the group,
 * 
 */
const selectableGroupUsers = computed(() => {
    const debtShareGroupUsers = props.debt.shares.map((share) => share.group_user);
    return props.group_users.filter((group_user) => {
        return !debtShareGroupUsers.some((debtShareGroupUser) => debtShareGroupUser.id === group_user.id);
    });
});

onMounted(() => {
    console.log(selectableGroupUsers.value)
})
</script>

<template>
    <div>
        <BigButton v-if="!showAddShare" @click="showAddShare = !showAddShare;refresh & refresh()">Add Share</BigButton>
        <Form
            v-if="showAddShare" 
            :action="route('share.store')"
            method="post"
            #default="{ errors }"
            resetOnSuccess
            :transform="data => ({ 
                ...data, 
                debt_id: props.debt.id,
                currency: props.debt.currency,
                // split even: take share value from last share (as first user takes remainders)
                // standard: multiply by 100 for minor units as the backend expects,
                // since we use Dinero.js in addDebt, it just isn't necessary here
                amount: props.debt.split_even.value ? props.debt.shares.pop().amount.amount * 100 : data.amount * 100,
            })"
            class="mt-4"
            @success="refresh & refresh()"
            @error="refresh & refresh()"
        >
            <select 
                name="group_user_id"
                id="group_user_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="" disabled selected>Select a user</option>
                <option 
                    v-for="group_user in selectableGroupUsers" 
                    :key="group_user.id" 
                    :value="group_user.id"
                >
                    {{ group_user.user.name }}
                </option>
            </select>
            <div v-if="!debt.split_even.value">
                <label for="amount" class="hidden">Amount</label>
                <input
                    step="0.01"
                    type="number"
                    id="amount"
                    name="amount"
                    class="w-full mt-2"
                    placeholder="Enter an amount"
                />
            </div>
            <label for="name" class="hidden">Name</label>
            <TextInput 
                type="text" 
                id="name"
                name="name"
                class="w-full mt-2"
                placeholder="Enter a share name"
            />
            <InputError class="mt-2" v-for="error in errors" :message="error" />
            <div class="flex flex-row mt-2 w-full sm:justify-end">
                <SecondaryButton
                    type="button"
                    class="mr-2"
                    @click="showAddShare = !showAddShare;refresh & refresh()"
                >
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                >
                    Save
                </PrimaryButton>
            </div>
        </Form> 
    </div>
</template>

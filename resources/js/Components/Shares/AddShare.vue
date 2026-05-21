<script setup>
import { onMounted, ref, inject, computed } from 'vue';
import InputError from '@/Components/Forms/InputError.vue';
import { Form } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import BigButton from '@/Components/Misc/BigButton.vue';
import GroupUserPicker from '@/Components/Forms/GroupUserPicker.vue';


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
const newShareGroupUser = ref(null);

/**
 * Initially get the group users that have shares for the debt.
 * If the debt is split, filter out the users that already have shares,
 * otherwise, all users are selectable.
 */
const selectableGroupUsers = computed(() => {
    const debtShareGroupUsers = props.debt.shares.map((share) => share.group_user);
    return props.debt.split_even.value 
        ? props.group_users.filter((group_user) => {
            return !debtShareGroupUsers.some((debtShareGroupUser) => debtShareGroupUser.id === group_user.id);
        }) 
        : props.group_users;
});

onMounted(() => {

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
                group_user_id: newShareGroupUser,
            })"
            class="mt-4"
            @success="refresh & refresh(); newShareGroupUser = null"
            @error="refresh & refresh()"
        >
            <GroupUserPicker
                :group_users="selectableGroupUsers"
                @userSelected="newShareGroupUser = $event"
            >
            </GroupUserPicker>
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

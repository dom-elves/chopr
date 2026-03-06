<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { store } from '@/debt.js';
import CurrencyPicker from '@/Components/Forms/CurrencyPicker.vue';
import UserPicker from '@/Components/Forms/UserPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/Forms/InputError.vue';
import Slider from '@/Components/Misc/Slider.vue';
import AddDebtFormShare from './AddDebtFormShare.vue';
import AddDebtFormName from './AddDebtFormName.vue';
import AddDebtFormAmount from './AddDebtFormAmount.vue';
import PrimaryButton from '@/Components/Misc/PrimaryButton.vue';
import SecondaryButton from '@/Components/Misc/SecondaryButton.vue';
import { useDebtStore } from '@/Stores/DebtStore';

const props = defineProps({
    groups: {
        type: Object,
    }
});


const emit = defineEmits(['closeModal']);

// vars
// groups set as a variable so they can be filtered
// selected group is done by a dropdown
const groups = ref(props.groups);
const selectedGroup = ref(null);

const addDebtForm = useForm({
// have to keep this temporarily set otherwise I can't debug on the go
});

const shareKey = ref(0);

/**
 * Currently everything sets to GBP so in the future, total balance can be sorted by curency
 * Might have to add a new table for balances per user
 */
function setSelectedCurrency(currency) {
    // store.addDebtForm.currency = currency.code;
    store.addDebtForm.currency = 'GBP';
}

/**
 * Toggles between the debt being split even/custom shares. Basically looks at total_amount if split, 
 * and then indiviudal fields if not. 
 */
function toggleSplitEven(toggle) {
    store.addDebtForm.split_even = toggle;

    if (store.addDebtForm.split_even) {
        store.splitEven();
    } else {
        store.calcTotalAmount()
    }
}

/**
 * Sets someone to 'own' the debt. This plays into how total balances are created.
 */
function setDebtOwner(userId) {
    store.addDebtForm.user_id = userId;
}

function setGroup(groupId) {
    useDebtStore().debtForm.group_id = groupId;
}

/**
 * Watch the change on user selected a group.
 * Find the group from those in the component props.
 * Set the shares with the values from the group users of the selected group.
 */
watch(() => useDebtStore().debtForm.group_id, (groupId) => {
    selectedGroup.value = groups.value.find((group) => group.id == groupId);
    useDebtStore().debtForm.user_shares = selectedGroup.value.group_users.map((group_user) => ({
        group_user_id: group_user.id,
        name: '',
        amount: 0,
    }));
});

function addDebt() {
    // remove all shares that are 0
    // empty string issue is sorted in the share component
    const filtered = store.addDebtForm.user_shares.filter((share) => share.amount != 0);
    addDebtForm.user_shares = filtered;
    
    addDebtForm.post(route('debt.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // name has to be reset as the models are actuall the store
            store.addDebtForm.name = '';
            emit('closeModal');
        },
        onError: (error) => {

        },
    })
}

onMounted(() => {
    // remove this when adding support for extra currencies
    store.addDebtForm.currency = 'GBP';
});

</script>
<template>
    <div class="p-6 flex flex-col">
        <form @submit.prevent="addDebt">
            <h2 class="text-lg font-medium text-gray-900 text-center sm:text-left">
                Add a new Debt
            </h2>
            <GroupPicker
                :groups="groups"
                :errors="addDebtForm.errors.group_id"
                @groupSelected="setGroup"
            >
            </GroupPicker>
            <AddDebtFormName
                :errors="addDebtForm.errors.name"
            >
            </AddDebtFormName>
            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="setSelectedCurrency"
            >
            </CurrencyPicker>
            <!-- after a group has been selected, we can display user picker and shares list -->
            <div v-if="selectedGroup">
                <UserPicker
                    :group_users="selectedGroup.group_users"
                    :errors="addDebtForm.errors.user_id"
                    @userSelected="setDebtOwner"
                >
                </UserPicker>
                <AddDebtFormShare
                    v-for="group_user in selectedGroup.group_users"
                    :key="`${shareKey} + ${group_user.id}`"
                    :group_user="group_user"
                >
                </AddDebtFormShare>
                <InputError class="mt-2" :message="addDebtForm.errors.user_shares" />
            </div> 
            <div class="flex flex-row mt-2 items-center justify-between">
                <div class="flex flex-col">
                    <p>Amount:</p>
                    <AddDebtFormAmount
                        :errors="addDebtForm.errors.amount"
                    >
                    </AddDebtFormAmount>
                </div>
                <Slider
                    label="Split even"
                    alignment="end"
                    size="xs"
                    @toggled="toggleSplitEven"
                >
                </Slider>
            </div>
            <div class="flex flex-row mt-4 justify-center sm:justify-end">
                <SecondaryButton 
                    type="button"
                    @click="emit('closeModal')"
                >
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                >
                    Save
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
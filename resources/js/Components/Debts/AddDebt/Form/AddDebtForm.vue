<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
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

/**
 * closeModal emit is for closing parent modal that contains the form.
 * 
 * selectedGroup is for when the user changes the group via the dropdown on the form,
 * it's stored as a separate ref as it also determines which users appear in the 
 * list for shares.
 */
const emit = defineEmits(['closeModal']);
const debtStore = useDebtStore();
const selectedGroup = ref(null);

/**
 * Watch the change on user selected a group.
 * Find the group from those in the component props.
 * Set the shares with the values from the group users of the selected group.
 */
watch(() => debtStore.debtForm.group_id, (groupId) => {
    selectedGroup.value = props.groups.find((group) => group.id == groupId);

    debtStore.debtForm.user_shares = selectedGroup.value.group_users.map((group_user) => ({
        group_user_id: group_user.id,
        user_name: group_user.user.name,
        share_name: '',
        amount: 0,
        checked: false,
    }));
});

/**
 * These aspects of the form have to work off an emit event pattern
 * as the components used are agnostic.
 *
 * setGroup uses the GroupPicker.
 *
 * setSelectedCurrency uses the CurrencyPicker, yet currently only works in GBP
 * so it doesn't actually do anything.
 *
 * setDebtOwner uses the UserPicker.
 *
 * toggleSplitEven takes the value of the toggle
 * and then either calls splitEven or calcTotalAmount from that.
 */
function setGroup(groupId) {
    debtStore.debtForm.group_id = groupId;
}

function setSelectedCurrency(currency) {
    debtStore.debtForm.currency = currency.code;
}

function setDebtOwner(userId) {
    debtStore.debtForm.user_id = userId;
}

function toggleSplitEven(toggle) {
    debtStore.debtForm.split_even = toggle;

    if (debtStore.debtForm.split_even) {
        debtStore.splitEven();
    } else {
        debtStore.calcTotalAmount()
    }
}

/**
 * Uses a promise in the store so closeModal can still be emitted from within the component.
 *
 * Essentially tries to run the debtStore.addDebt call, failing shows form errors,
 * success then calls closeModal.
 */
async function addDebt() {
    try {
        await debtStore.addDebt();
        emit('closeModal');
    } catch (errors) {

    }
}

</script>
<template>
    <div class="p-6 flex flex-col">
        <form @submit.prevent="addDebt">
            <h2 class="text-lg font-medium text-gray-900 text-center sm:text-left">
                Add a new Debt
            </h2>
            <GroupPicker
                :groups="groups"
                :errors="debtStore.debtForm.errors.group_id"
                @groupSelected="setGroup"
            >
            </GroupPicker>
            <AddDebtFormName
                :errors="debtStore.debtForm.errors.name"
            >
            </AddDebtFormName>
            <CurrencyPicker
                :errors="debtStore.debtForm.errors.currency"
                @currencySelected="setSelectedCurrency"
            >
            </CurrencyPicker>
            <!-- after a group has been selected, we can display user picker and shares list -->
            <div v-if="selectedGroup">
                <UserPicker
                    :group_users="selectedGroup.group_users"
                    label="This will be the owner of the debt"
                    :errors="debtStore.debtForm.errors.user_id"
                    @userSelected="setDebtOwner"
                >
                </UserPicker>
                <AddDebtFormShare
                    v-for="(share, index) in debtStore.debtForm.user_shares"
                    :key="`${share.group_user_id}`"
                    :index="index"
                >
                </AddDebtFormShare>
                <InputError class="mt-2" :message="debtStore.debtForm.errors.user_shares" />
            </div> 
            <div class="flex flex-row mt-2 items-center justify-between">
                <div class="flex flex-col">
                    <p>Amount:</p>
                    <AddDebtFormAmount
                        :errors="debtStore.debtForm.errors.amount"
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
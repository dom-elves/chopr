<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/InputError.vue';
import Slider from '@/Components/Slider.vue';
import AddDebtFormShare from './AddDebtFormShare.vue';
import AddDebtFormName from './AddDebtFormName.vue';

const props = defineProps({
    groups: {
        type: Object,
    }
});

// vars
// groups set as a variable so they can be filtered
// selected group is done by a dropdown
const groups = ref(props.groups);
const selectedGroup = ref(null);

// the form
const addDebtForm = useForm({
    // neutral properties
    user_id: usePage().props.auth.user.id, 
    group_id: null,
    name: '',
    currency: '',


    // toggleables
    user_shares: [],
    // user_share_names: {},
    split_even: false,
    // amount is shared between the toggleables, but is reset each time toggle is done
    amount: 0,
});

// will show instances of AddDebtFormShare after a group is selected
function updateSelectedGroup(groupId) {
    // addDebtForm.reset('user_ids');
    selectedGroup.value = groups.value.find((group) => group.id == groupId);
    addDebtForm.group_id = selectedGroup.value.id;
}

// name
function updateDebtName(debtName) {
    addDebtForm.name = debtName;
    console.log(addDebtForm);
}

// currency 
function updateSelectedCurrency(currency) {
    // todo: figure out a way to send the whole object so form UI can be improved
    // currently set to GBP to avoid errors when calcing total
    addDebtForm.currency = 'GBP';
    console.log(addDebtForm);
}

</script>
<template>
    <div>
        <form>
            <GroupPicker
                :groups="groups"
                :errors="addDebtForm.errors.group_id"
                @groupSelected="updateSelectedGroup"
            >
            </GroupPicker>
            <AddDebtFormName
                :groups="groups"
                :errors="addDebtForm.errors.group_id"
                @debtNameEntered="updateDebtName"
            >
            </AddDebtFormName>
            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="updateSelectedCurrency"
            >
            </CurrencyPicker>
        </form>
    </div>
</template>
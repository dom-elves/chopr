<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/InputError.vue';
import Slider from '@/Components/Slider.vue';
import AddDebtShare from './AddDebtFormShare.vue';

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
    group_id: null,
    user_id: usePage().props.auth.user.id, 
    currency: '',
    name: null,
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
        </form>
    </div>
</template>
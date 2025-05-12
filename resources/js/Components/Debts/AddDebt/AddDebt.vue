<script setup>
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import CurrencyPicker from '@/Components/CurrencyPicker.vue';
import GroupPicker from '@/Components/Groups/GroupPicker.vue';
import InputError from '@/Components/InputError.vue';
import Slider from '@/Components/Slider.vue';
import AddDebtShare from './AddDebtShare.vue';

// props
const props = defineProps({
    groups: {
        type: Object,
    }
});

// groups set as a variable so they can be filtered
// selected group is done by a dropdown
const groups = ref(props.groups);
const selectedGroup = ref(null);
// has to be a separate variable so it can be displayed to the user
// could be applied with .innerHTML but i'm pretty sure this is better
const splitEvenShare = ref(0);

// hopefully key doesn't break anything
// as it *should* only be used as a hack to refresh shares on split even toggle
const shareKey = ref(0);

// the form
const addDebtForm = useForm({
    // neutral properties
    group_id: null,
    user_id: usePage().props.auth.user.id, 
    currency: '',
    name: null,
    // toggleables
    user_ids: {},
    user_share_names: {},
    split_even: false,
    // amount is shared between the toggleables, but is reset each time toggle is done
    amount: 0,
});

// form  methods
// split even
function toggleSplitEven(toggle) {
    console.log(toggle);
    addDebtForm.split_even = toggle;
    addDebtForm.reset('user_ids', 'amount', 'user_share_names');
    shareKey.value++
}

// currency 
function updateSelectedCurrency(currency) {
    // todo: figure out a way to send the whole object so form UI can be improved
    // currently set to GBP to avoid errors when calcing total
    addDebtForm.currency = 'GBP';
}

// group
function updateSelectedGroup(groupId) {
    addDebtForm.reset('user_ids');
    selectedGroup.value = groups.value.find((group) => group.id == groupId);
    addDebtForm.group_id = selectedGroup.value.id;
}

// update debt total
// pass in input value & key from loop to get correct input change
// then add together the total values of the user_ids obj
function updateDebtAmount() {
    addDebtForm.amount = Object.values(addDebtForm.user_ids)
        .reduce((acc, value) => acc + value, 0);
}

// this runs on user selection & total amount entry/change
function splitEven() {
    // we don't split the amount if the debt isn't splitEven
    if (!addDebtForm.split_even) {
        return;
    }

    // total users being added 
    const totalSelectedUsers = Object.keys(addDebtForm.user_ids).length;
    // rounded share to 2 dp
    splitEvenShare.value = Math.floor((addDebtForm.amount / totalSelectedUsers) * 100) / 100;
    // updating the object status from 'true' (selected) to the share amount
    addDebtForm.user_ids = Object.fromEntries(
        Object.keys(addDebtForm.user_ids).map(key => [key, splitEvenShare.value])
    );
    
    // remainder of what's lost when rounding to 2 dp
    const remainder = ((addDebtForm.amount - (splitEvenShare.value * totalSelectedUsers))).toFixed(2);
    // first user in the object is unlucky, gets given the remainder (a matter pennies)
    const first = Object.keys(addDebtForm.user_ids)[0];
    addDebtForm.user_ids[first] = (splitEvenShare.value + Number(remainder));
}

// post the debt
function addDebt() {
    // filter out entires that are 0
    // prevents shares for 0 money being added
    const filtered = Object.fromEntries(
        Object.entries(addDebtForm.user_ids).filter(([key, value]) => value !== 0)
    );

    addDebtForm.user_ids = filtered;

    addDebtForm.post(route('debt.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // reset properties that user will likely not want to add again immediately
            addDebtForm.reset('user_ids', 'amount', 'name', 'user_share_names');
        },
        onError: (error) => {

        },
    })
}

function buildForm(userData) {
    console.log('2', userData);
}

</script>

<template>
    <div class="py-4 my-2 border-solid border-2 border-green-600 bg-white flex flex-col">
        <!-- start of form -->
        <form @submit.prevent="addDebt" class="p-2">
            <!-- group picker -->
            <GroupPicker
                :groups="groups"
                :errors="addDebtForm.errors.group_id"
                @groupSelected="updateSelectedGroup"
            >
            </GroupPicker>
            <!-- debt name -->
            <div class="py-2">
                <label 
                    for="debt-name" 
                    class="block text-sm font-medium text-gray-700 hidden"
                    id="debtName"
                >
                    Debt Name
                </label>
                <input
                    v-model="addDebtForm.name" 
                    type="text" 
                    id="debt-name" 
                    name="debt-name" 
                    class="w-full"
                    placeholder="Debt Name"
                    aria-labelledby="debtName"
                />
                <InputError class="mt-2" :message="addDebtForm.errors.name" />
            </div>
            <!-- currency picker -->
            <CurrencyPicker
                :errors="addDebtForm.errors.currency"
                @currencySelected="updateSelectedCurrency"
            >
            </CurrencyPicker>
            <div v-if="selectedGroup" class="py-2 flex-col">
    
                <div v-for="group_user in selectedGroup.group_users">
                    <AddDebtShare
                        :key="shareKey + group_user.id"
                        :group_user="group_user"
                        :split_even="addDebtForm.split_even"
                        @updateShare="buildForm"
                    >
                    </AddDebtShare>
                </div>
                
                <!-- non split even users
                <div v-if="!addDebtForm.split_even">
                    <div v-for="group_user in selectedGroup.group_users"
                        class="flex flex-row justify-between items-center" 
                        style="height:70px"
                    >
                        <p>
                            {{ group_user.user.name }}{{  group_user.user_id }}
                        </p>
                        <label :for="`share-name-${group_user.id}`">
                            Share name
                        </label>
                        <input
                            type="text"
                            class="w-1/4"
                            :id="group_user.user_id"
                            :name="`share-name-${group_user.id}`"
                            v-model="addDebtForm.user_share_names[group_user.user_id]"
                        >
                        <label
                            class="hidden" 
                            :for="group_user.user_id"
                        >
                            Amount
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            class="w-1/4"
                            :id="group_user.user_id"
                            :name="`group_user-${group_user.id}`"
                            v-model="addDebtForm.user_ids[group_user.user_id]"
                            @change="updateDebtAmount" 
                        >
                    </div>
                </div>
                
             split even users
                <div v-else>
                    <div v-for="group_user in selectedGroup.group_users"
                        class="flex flex-row justify-between items-center" 
                        style="height:70px"
                    >
                    <p>{{ group_user.user.name }}{{  group_user.user_id }}</p>
                    
                        <label :for="`share-name-${group_user.id}`">
                            Share name
                        </label>
                        <input
                            type="text"
                            class="w-1/4"
                            :id="group_user.user_id"
                            :name="`share-name-${group_user.id}`"
                            v-model="addDebtForm.user_share_names[group_user.user_id]"
                        >
                    
                        <label 
                            class="hidden"
                            :for="`${group_user.user_id}-split_even-selected`"
                        >
                            Amount
                        </label>
                        <p v-if="addDebtForm.user_ids[group_user.user_id]">
                            {{ splitEvenShare }}
                        </p>
                        <input
                            type="checkbox"
                            :id="`${group_user.user_id}-split_even-selected`"
                            @change="splitEven"
                            v-model="addDebtForm.user_ids[group_user.user_id]"
                        >
                    </div>
                </div> -->
                <div class="flex justify-end">
                    <InputError class="mt-2" :message="addDebtForm.errors.user_ids" />
                </div>
                <!-- split even toggle & total amount -->
                <div class="flex flex-row justify-between items-center py-2">
                    <Slider
                        label="Split even?"
                        @toggled="toggleSplitEven"
                    >
                    </Slider>
                    <div>
                        <label for="amount">
                            Total:
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            style="width:120px"
                            id="amount"
                            name="amount"
                            v-model="addDebtForm.amount"
                            :disabled="!addDebtForm.split_even"
                            @change="splitEven"
                        >
                    </div>
                </div>
                <div class="flex justify-end">
                    <InputError class="mt-2" :message="addDebtForm.errors.amount" />
                </div>
            </div>
            
            <button class="bg-blue-400 text-white py-2 w-full" type="submit">Save</button>
        </form>
    </div>
</template>
<style>

</style>
import { defineStore } from 'pinia';
import { usePage, router, useForm } from '@inertiajs/vue3';

export const useDebtStore = defineStore('debtStore', {
    state: () => ({
        debtForm: useForm({
            group_id: null,
            name: '',
            user_id: null,
            currency: 'GBP', // todo: set this to '' when adding support for all currencies
            user_shares: [], 
            split_even: false,
            amount: 0,
        }),
    }),
    actions: {
        setGroup(groupId) {
            this.debtForm.group_id = groupId;
        }
    }
})
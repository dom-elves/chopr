import { defineStore } from 'pinia';
import { usePage, router, useForm } from '@inertiajs/vue3';

export const useDebtStore = defineStore('debtStore', {
    state: () => ({
        debtForm: useForm({
            user_id: usePage().props.auth.user.id,
            group_id: null,
            name: '',
            currency: '',
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
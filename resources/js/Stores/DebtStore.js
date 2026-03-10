import { defineStore } from 'pinia';
import { useForm } from '@inertiajs/vue3';
import Dinero from 'dinero.js'

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
        /**
         * Map and reduce the share.amounts to calc the debt total.
         * Set as a Dinero object as that's what the splitEven method needs.
         */
        calcTotalAmount() {
            this.debtForm.amount = Dinero({
                amount: this.debtForm.user_shares.map((share) => share.amount)
                    .reduce((acc, value) => acc + value, 0),
                currency: this.debtForm.currency,
            }).getAmount();
        },

        /**
         *
         */
        splitEven() {

        },
        /*
         * Using a promise so the component can emit closeModal.
         *
         * Success lands you in the closeModal emit block (try).
         * Errors are still displayed via the form helper,
         * but can be passed back if necessary.
         */
        async addDebt() {
            return new Promise((resolve, reject) => {
                this.debtForm.post(route('debt.store'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.debtForm.name = '';
                        resolve();
                    },
                    onError: (errors) => {
                        reject();
                    },
                });
            });
        },
    }
})
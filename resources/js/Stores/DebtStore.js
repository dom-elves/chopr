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
         * Filter down unchecked/0 value shares to find how many users are in the debt.
         * Set an array of splits (e.g. 3 users is [1, 1, 1]),
         * as that's what Dinero needs to allocate split proportions,
         * [1, 1, 1] is 33% each, [1, 2, 1] would be 25%, 50%, 25%.
         * 
         * The shares const then becomes an array like [500, 500, 500],
         * for a £15 debt split 3 ways.
         */
        splitEven() {
            console.log('a', this.debtForm);
            const selectedUsersLength = this.debtForm.user_shares.filter((share) => 
                share.checked == true).length;
            
            const splits = [];

            for (let i = selectedUsersLength; i > 0; i--) {
                splits.push(1);
            }
            // todo: add an error here, maybe make it try catch
    
            const shares = Dinero({
                amount: this.debtForm.amount,
                currency: this.debtForm.currency,
            })
            .allocate(splits)
            .map((share) => share.getAmount());


            console.log('shares', shares);
            // set as share amount for the user if they are selected
            this.debtForm.user_shares.forEach((share) => {
                share.amount = 0;
    
                if (share.checked) {
                    share.amount = shares.shift();
                }
            });
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
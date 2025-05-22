import { reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

export const store = reactive({
    addDebtForm: {
        user_id: null,
        group_id: null,
        name: '',
        currency: '',
        user_shares: [], 
        split_even: false,
        amount: 0,
    },

    /**
     * Adds share amounts together on input change for non split even debts
     */
    calcTotalAmount() {
        this.addDebtForm.amount = this.addDebtForm.user_shares.map(share => share.amount)
            .reduce((acc, value) => acc + value, 0).toFixed(2);

        console.log('form after calc total', this.addDebtForm);
    },

    /**
     * Split even only runs on input change if the form is set to split even
     * On a non split even debt, only the model is changing so this method never fires
     */
    splitEven() {
        // total users being added 
        const selectedUsersLength = this.addDebtForm.user_shares.filter((share) => share.checked == true).length;
    
        // rounded share to 2 dp
        const amount = (Math.floor((this.addDebtForm.amount / selectedUsersLength) * 100) / 100);

        // set as share amount for the user if they are selected
        this.addDebtForm.user_shares.forEach((share) => {
            share.amount = 0;
            if (share.checked) {
                share.amount = amount;
            }
        });

        // add the rounded shares together 
        const shareTotal = this.addDebtForm.user_shares.map((share) => share.amount)
                .reduce((acc, value) => acc + value, 0);

        // subtract from amount to find remainder
        const remainder = ((this.addDebtForm.amount - shareTotal)).toFixed(2);

        // then tack it on to the first user, if someone is selected
        try {
            this.addDebtForm.user_shares.find((share) => share.checked).amount += Number(remainder);
        } catch (e) {
            // set an error here later 
        }   

        console.log('form after split even', this.addDebtForm);
    },
})
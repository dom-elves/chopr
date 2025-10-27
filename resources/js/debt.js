import { reactive } from 'vue';
import Dinero from 'dinero.js'

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
        const amount = Dinero({
            amount: this.addDebtForm.user_shares.map(share => share.amount)
                .reduce((acc, value) => acc + value, 0),
            currency: this.addDebtForm.currency,
        })

        this.addDebtForm.amount = amount.getAmount();
    },

    /**
     * Split even only runs on input change if the form is set to split even
     * On a non split even debt, only the model is changing so this method never fires
     */
    splitEven() {
        // total users being added 
        const selectedUsersLength = this.addDebtForm.user_shares.filter((share) => share.checked == true).length;
        
        const amount = Dinero({
            amount: this.addDebtForm.amount * 100,
            currency: this.addDebtForm.currency,
        });

        const splits = [];

        // this is how Dinero.js wants the splits to be defined
        // e.g. a 3 person split is [1, 1, 1]
        for (let i = selectedUsersLength; i > 0; i--) {
            splits.push(1);
        }

        // handling the allocation of splits
        const shares = amount.allocate(splits).map(s => s.getAmount());

        // set as share amount for the user if they are selected
        // by default, give the first user the slightly larger share
        this.addDebtForm.user_shares.forEach((share) => {
            share.amount = 0;

            if (share.checked) {
                share.amount = shares.shift() / 100;
            }
        });
    },
})
import { reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

export const store = reactive({
    addDebtForm: {
        // neutral properties
        user_id: null,
        group_id: null,
        name: '',
        currency: '',
        user_shares: [], // built in updateUserShares()
        split_even: false,
        amount: 0,
    }
})
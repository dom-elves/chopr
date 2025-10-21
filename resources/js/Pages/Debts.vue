<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Debt from '@/Components/Debts/Debt.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AddDebtForm from '@/Components/Debts/AddDebt/Form/AddDebtForm.vue';
import BigButton from '@/Components/BigButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    debts: {
        type: Object,
    },
    status: {
        type: String,
    },
    groups: {
        type: Object,
    }
});

// for toggling form display
const showAddDebt = ref(false);

onMounted(() => {
    console.log('here', props.debts);
});
</script>

<template>
    <Head title="Debts" /> 
    <AuthenticatedLayout
        :status="status"
        :user_balance="user_balance"
    >
        <!-- <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template> -->
        <div class="flex flex-col p-2">
            <BigButton 
                @click="showAddDebt = !showAddDebt"
            >
                Add a debt
            </BigButton>
            <Debt
                v-for="debt in debts"
                :debt="debt"
            >
            </Debt>
            <Modal :show="showAddDebt" @close="showAddDebt = false" @addDebt="showAddDebt = false">
                <AddDebtForm
                    v-if="showAddDebt"
                    :groups="groups"
                    @closeModal="showAddDebt = false"
                >
                </AddDebtForm>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>

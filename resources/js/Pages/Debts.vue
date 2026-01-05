<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Debt from '@/Components/Debts/Debt.vue';
import { Head, usePage, InfiniteScroll} from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AddDebtForm from '@/Components/Debts/AddDebt/Form/AddDebtForm.vue';
import BigButton from '@/Components/Misc/BigButton.vue';
import Modal from '@/Components/Forms/Modal.vue';

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

});
</script>

<template>
    <Head title="Debts" /> 
    <AuthenticatedLayout
        :status="status"
    >
        <!-- <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template> -->
        <BigButton 
            @click="showAddDebt = !showAddDebt"
        >
            Add a debt
        </BigButton>
        <InfiniteScroll data="debts" :buffer="100">
            <Debt
                v-for="debt in debts.data"
                :debt="debt"
                :key="debt.id"
            >
            </Debt>
        </InfiniteScroll>
        <Modal :show="showAddDebt" @close="showAddDebt = false" @addDebt="showAddDebt = false">
            <AddDebtForm
                v-if="showAddDebt"
                :groups="groups.data"
                @closeModal="showAddDebt = false"
            >
            </AddDebtForm>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Share from '@/Components/Shares/Share.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});
const confirmingDebtDeletion = ref(false);

const confirmDebtDeletion = () => {
    confirmingDebtDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

function deleteDebt() {
    console.log('delete', props.debt.id);
    router.delete(route('debt.destroy', { debt_id: props.debt.id }));
}

const closeModal = () => {
    confirmingDebtDeletion.value = false;
};

</script>

<template>
    <div class="m-2 border-solid border-2 border-amber-600">
        <div class="flex flex-row justify-between">
            <p> {{ debt.name }} {{ debt.amount }}</p>
            <div class="border-solid border-2 border-red-600" @click="confirmDebtDeletion">X</div>
        </div>
        <div class="flex flex-row flex-wrap justify-evenly">
            <Share
                v-for="shares in debt.shares"
                :share="shares"
            >
            </Share>
        </div>
        <Modal :show="confirmingDebtDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Are you sure you want to delete this debt?
                </h2>
                <div class="mt-6 flex justify-end">
                    <button 
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        class="ms-3"
                        @click="deleteDebt"
                    >
                        Delete Debt
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Share from '@/Components/Shares/Share.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});

function deleteDebt() {
    router.post(route('debt.destroy'), props.debt.id);
}

</script>

<template>
    <div class="m-2 border-solid border-2 border-amber-600">
        <div class="flex flex-row justify-between">
            <p> {{ debt.name }} £{{ debt.amount }}</p>
            <div class="border-solid border-2 border-red-600" @click="deleteDebt">X</div>
        </div>
        <div class="flex flex-row flex-wrap justify-evenly">
            <Share
                v-for="shares in debt.shares"
                :share="shares"
            >
            </Share>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import Share from '@/Components/Shares/Share.vue';

const props = defineProps({
    debt: {
        type: Object,
    },
});

</script>

<template>
    <div class="m-2 border-solid border-2 border-amber-600">
        <p> {{ debt.name }} £{{ debt.amount }}</p>
        <div class="flex flex-row flex-wrap justify-evenly">
            <Share
                v-for="shares in debt.shares"
                :share="shares"
            >
            </Share>
        </div>
    </div>
</template>

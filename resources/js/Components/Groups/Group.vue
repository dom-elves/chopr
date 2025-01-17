<script setup>
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import Debt from '@/Components/Debts/Debt.vue';
import AddDebt from '@/Components/Debts/AddDebt.vue';

const props = defineProps({
    group: {
        type: Object,
    },
});

const groupUsers = reactive({

});

</script>

<template>
    <div class="py-4 m-2 border-solid border-2 border-indigo-600">
        <h3> {{ group.name }}</h3>
        <Debt
            v-for="debt in group.debts"
            :debt="debt"
        >
        </Debt>
        <AddDebt
            :group-users="group.group_users"
            :group-id="group.id"
          >
        </AddDebt>
    </div>
</template>

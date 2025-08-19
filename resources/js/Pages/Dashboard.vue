<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Group from '@/Components/Groups/Group.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AddDebt from '@/Components/Debts/AddDebt/AddDebt.vue';

const props = defineProps({
    groups: {
        type: Object,
    },
    status: {
        type: String,
    },
});

// for toggling form display
const showAddDebt = ref(false);

onMounted(() => {
    console.log(props.status);
});
</script>

<template>
    <Head title="Dashboard" /> 
    <AuthenticatedLayout
        :status="status"
        :user_balance="user_balance"
    >
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>
        <button 
            @click="showAddDebt = !showAddDebt"
            class="bg-blue-400 text-white p-2 w-full"
        >
            Add a debt
        </button>
        <AddDebt
            v-if="showAddDebt"
            :groups="groups"
        >
        </AddDebt>
        <Group
            v-for="group in groups"
            :group="group"
        >
        </Group>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Group from '@/Components/Groups/Group.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AddDebt from '@/Components/Debts/AddDebt.vue';

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

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        You're logged in! This is hardcoded.
                    </div>
                </div>
            </div>
        </div>

        <div  v-if="props.status" class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        {{ props.status }}
                    </div>
                </div>
            </div>
        </div>

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

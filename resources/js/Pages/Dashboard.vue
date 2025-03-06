<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Group from '@/Components/Groups/Group.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    groups: {
        type: Object,
    },
    status: {
        type: String,
    },
});

onMounted(() => {
    // console.log('groups', props.groups);
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
        <div class="p-4">
            <Group
                v-for="group in groups"
                :group="group"
            >
            </Group>
        </div>
    </AuthenticatedLayout>
</template>

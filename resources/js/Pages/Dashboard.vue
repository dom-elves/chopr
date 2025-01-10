<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    data: {
        type: Object,
    },
});

onMounted(() => console.log(props));

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
                        You're logged in!
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div v-for="data in data">
                <p> {{ data }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

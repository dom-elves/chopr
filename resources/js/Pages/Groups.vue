<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ViewGroup from '@/Components/Groups/ViewGroup.vue';
import CreateGroup from '@/Components/Groups/CreateGroup.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';

const props = defineProps({
    groups: {
        type: Object,
    },
    status: {
        type: String,
    },
});

</script>

<template>
    <div>
        <Head title="Groups" />
        <AuthenticatedLayout
            :status="status"
            >
            <CreateGroup>
            </CreateGroup>
            <div class="flex flex-col sm:flex-row sm:flex-wrap ">
                <ViewGroup
                    v-for="group in groups"
                    :key="group.id"
                    :group="group"
                    class="sm:w-1/2 lg:w-1/3"
                />
            </div>
        </AuthenticatedLayout>
    </div>
</template>

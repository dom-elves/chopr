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
});
// onMounted(() => console.log('actual?', props, 'page?', usePage().props));
</script>

<template>
    <div>
        <Head title="Groups" />
        <AuthenticatedLayout>
            <ViewGroup
                v-for="group in groups"
                :group="group"
            >
            </ViewGroup>
            <CreateGroup>
            </CreateGroup>
        </AuthenticatedLayout>
    </div>
</template>

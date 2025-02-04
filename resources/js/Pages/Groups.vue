<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ViewGroup from '@/Components/Groups/ViewGroup.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';

const props = defineProps({
    groups: {
        type: Object,
    },
});

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
        </AuthenticatedLayout>
    </div>
</template>

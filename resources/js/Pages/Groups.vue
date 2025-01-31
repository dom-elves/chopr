<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ViewGroups from '@/Components/Groups/ViewGroups.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';

const props = defineProps({
    groups: {
        type: Object,
    },
});

onMounted(() => console.log(props.groups));


</script>

<template>
    <div>
        <Head title="Groups" />
        <AuthenticatedLayout>
            <ViewGroups
                :groups="groups"
            >
            </ViewGroups>
        </AuthenticatedLayout>
    </div>
</template>

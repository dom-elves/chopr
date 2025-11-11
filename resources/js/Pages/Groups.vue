<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Group from '@/Components/Groups/Group.vue';
import CreateGroup from '@/Components/Groups/CreateGroup.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';

const props = defineProps({
    groups: {
        type: Object,
    },
    status: {
        type: String,
    },
});

onMounted(() => {
    console.log(props.groups);
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
            <Group
                v-for="group in groups"
                :group="group"
            />
        </AuthenticatedLayout>
    </div>
</template>

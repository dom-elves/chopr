<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Group from '@/Components/Groups/Group.vue';
import CreateGroup from '@/Components/Groups/CreateGroup.vue';
import { Head, InfiniteScroll } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import InfiniteScrollControls from '@/Components/Misc/InfiniteScrollControls.vue';

const props = defineProps({
    groups: {
        type: Object,
    },
    status: {
        type: String,
    },
});

onMounted(() => {

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
            <InfiniteScrollControls data="groups">
                <Group
                    v-for="group in groups.data"
                    :group="group"
                    :key="group.id"
                />
            </InfiniteScrollControls>
        </AuthenticatedLayout>
    </div>
</template>

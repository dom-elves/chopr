<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Group from '@/Components/Groups/Group.vue';
import CreateGroup from '@/Components/Groups/CreateGroup.vue';
import { Head, InfiniteScroll } from '@inertiajs/vue3';
import { onMounted } from 'vue';

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
            <InfiniteScroll data="groups" :buffer="100">
                <Group
                    v-for="group in groups.data"
                    :group="group"
                    :key="group.id"
                />
            </InfiniteScroll>
        </AuthenticatedLayout>
    </div>
</template>

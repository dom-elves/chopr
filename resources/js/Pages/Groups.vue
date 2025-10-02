<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ViewGroup from '@/Components/Groups/ViewGroup.vue';
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

});

</script>

<template>
    <div>
        <Head title="Groups" />
        <AuthenticatedLayout
            :status="status"
        >
            <div class="flex flex-col p-2">
                <CreateGroup>
                </CreateGroup>
            
                <ViewGroup
                    v-for="group in groups"
                    :group="group"
                />
            </div>
        </AuthenticatedLayout>
    </div>
</template>

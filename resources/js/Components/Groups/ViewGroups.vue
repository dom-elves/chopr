<script setup>

import { computed, onMounted, onUnmounted, ref, reactive } from 'vue';
import GroupUser from '@/Components/GroupUsers/GroupUser.vue';
const props = defineProps({
    groups: {
        type: Object,
    },
});

onMounted(() => console.log(props.groups));


</script>

<template>
    <div>
        <!-- 
            add group component (with email invite)
            loop over groups to view
                group user component
                maybe into user component?
        -->
                <div 
                    v-for="group in groups"
                    class="p-4 m-2 border-solid border-2 border-indigo-600"
                >
                    <h3 class="text-3xl text-center mb-4">
                        {{ group.name }}
                    </h3>
                    <GroupUser
                        v-for="group_user in group.group_users"
                        :group_user="group_user"
                        >
                    </GroupUser>
                </div>
    </div>
</template>
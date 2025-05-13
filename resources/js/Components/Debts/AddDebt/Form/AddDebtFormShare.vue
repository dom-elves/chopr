<script setup>
import { onMounted, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

// props
const props = defineProps({
    group_user: {
        type: Object,
    },
    split_even: {
        type: Boolean,
    },
});

const share = ref({
    user_id: props.group_user.user_id,
    group_user_id: props.group_user.id,
    name: '',
    amount: null,
});

const emit = defineEmits(['updateShare']);

function updateShare() {
    console.log(share.value);
    emit('updateShare', share.value);
}

onMounted(() => console.log(props.split_even));

</script>
<template>
    <div class="flex">
        <p>
            {{ group_user.user.name }}{{ group_user.user_id }}
        </p>
        <div>
            <label 
                :for="`share-name-${group_user.id}`"
            >
                Share name:
            </label>
            <input 
                type="text"
                :id="`share-name-${group_user.id}`"
                :name="`share-name-${group_user.id}`"
                v-model="share.name"
                @change="updateShare"
            >
        </div>
        <div v-if="!split_even">
            <label 
                :for="`share-amount-${group_user.id}`"
            >
                Amount
            </label>
            <input 
                type="number"
                step="0.01"
                :id="`share-amount-${group_user.id}`"
                :name="`share-amount-${group_user.id}`"
                v-model="share.amount"
                @change="updateShare"
            >
        </div>
        <div v-else>
            <label
                :for="`share-amount-split-even${group_user.id}`"
                class="hidden"
            >
                Amount
            </label>
            <p>{{ share.amount }}</p>
            <input
                type="checkbox"
                :id="`share-amount-split-even${group_user.id}`"
            >
        </div>
    </div>
</template>
<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    groupUser: {
        type: Object,
    },
    share: {
        type: Number,
    }
});

const selected = ref(false);

function splitSharesEvenly(v) {
    console.log(v);
}

// onMounted(() => console.log('share', props.groupUser));

</script>

<template>
    <div
        @click="selected = !selected"
        class="border-solid border-2 border-green-600 my-2 p-1"
        :class="selected ? 'bg-green-200' : ''"
    >
        <form class="flex flex-row justify-between items-center" style="height:70px">
            <label 
                :for="groupUser.id">{{ groupUser.user.name }}
            </label>
            <input
                :name="`groupUser-${groupUser.id}`"
                @click.stop
                @submit.prevent
                v-show="selected"
                type="number"
                step="0.01" 
                :id="groupUser.id"
                class="w-1/4 disabled:bg-slate-50"
                @change="$emit('emitShare', groupUser.id, Number($event.target.value))"
                
                
            />
        </form>
    </div>
</template>

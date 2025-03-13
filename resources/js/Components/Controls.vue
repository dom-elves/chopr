<script setup>
import { ref } from 'vue';

const props = defineProps({
    item: {
        type: String,
    },
});

const showMenu = ref(false);

</script>
<template>
    <div class="flex flex-col">
        <i class="fa-solid fa-ellipsis-vertical" @click="showMenu = !showMenu"></i>
        <div v-if="showMenu">
            <ul>
                <li @click="$emit(`edit${props.item}`)">Edit {{ props.item }}</li>
                <li @click="$emit(`delete${props.item}`)">Delete {{ props.item }}</li>
            </ul>
        </div>
    </div>
</template>
<style>
li {
    background-color: white;
}
li:hover {
    background-color: #c5c7c9;
}
</style>
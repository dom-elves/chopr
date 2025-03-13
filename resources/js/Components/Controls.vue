<script setup>
import { ref, defineEmits } from 'vue';

const emit = defineEmits(['editItem', 'deleteItem']);

const props = defineProps({
    item: {
        type: String,
    }
});

const showMenu = ref(false);

function editItem() {
    emit(`edit${props.item}`);
    showMenu.value = false;
}

function deleteItem() {
    emit(`delete${props.item}`);
    showMenu.value = false;
}

</script>
<template>
    <div class="flex flex-col" @click="showMenu = !showMenu">
        <i class="fa-solid fa-ellipsis-vertical"></i>
        <div v-if="showMenu">
            <ul>
                <li @click="editItem">Edit {{ props.item }}</li>
                <li @click="deleteItem">Delete {{ props.item }}</li>
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
<script setup>

import { InfiniteScroll, usePage } from '@inertiajs/vue3';
import { onMounted, watch, ref } from 'vue';

/**
 * Pass in the string of the item you're scrolling
 * assuming it's being returned from the Inertia::scroll method
 * as that means it'll come back as e.g. debts.data
 * so you pass in the string to InfiniteScroll to basically tell it what to look for.
 */
const props = defineProps({
    data: {
        type: String,
        required: true,
    }
});

/**
 * If the user scrolls past the height of the page, eventually show top controls.
 */
const pageHeight = ref(window.innerHeight);

/**
 * Basic logic to make the "back to top" button appear depending on scrollpos.
 */
const scrollY = ref(0)

function onScroll() {
  scrollY.value = window.scrollY
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll);
    console.log(usePage().props);
})

</script>
<template>
    <div>
        <InfiniteScroll :data="data" :buffer="500" :manual-after="1">
            <template #previous="{ loading, hasPrevious }">
                <div class="flex flex-row items-center justify-center">
                    <Transition name="fade">
                        <button
                            v-if="scrollY > pageHeight"
                            @click="scrollToTop"
                            :disabled="loading"
                            class="top font-semibold"
                        >
                            {{ loading ? 'Loading...' : 'Back to top' }}
                        </button>
                    </Transition>
                </div>
            </template>
            <!-- slot is for whatever your prop string is, what will be looped over in parent -->
            <slot />
            <template #next="{ loading, fetch, hasMore }">
                <div class="grid grid-cols-3 items-center">
                    <div>

                    </div>
                    <button
                        v-if="hasMore"
                        @click="fetch"
                        :disabled="loading"
                        class="bottom font-semibold justify-self-center w-full"
                    >
                        {{ loading ? 'Loading...' : 'Load more' }}
                    </button>
                    <p class="font-semibold justify-self-end tracking-tight">
                        {{ usePage().props[data].data.length }} of {{ usePage().props[data].meta.total }}
                    </p>
                </div>
            </template>
        </InfiniteScroll>
    </div>
</template>
<style scoped>
.top {
    position: fixed;
    box-shadow: 0 10px 6px rgba(0, 0, 0, 0.1);
    padding: 6px;
    background-color:black;
    color: white;
    border-radius: 4px;
}

.top:hover {
    background-color: rgb(55 65 81);
    border: 2px solid  rgb(55 65 81);
}

.bottom {
    padding: 6px;
    background-color: black;
    border-radius: 4px;
    border: 2px solid black;
    color: white
}

.bottom:hover {
    background-color: rgb(55 65 81);
    border: 2px solid  rgb(55 65 81);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.1s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
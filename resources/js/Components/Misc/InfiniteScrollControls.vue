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
 * Basic logic to make the "back to top" button appear depending on scrollpos
 */
const scrollY = ref(0)

function onScroll() {
  scrollY.value = window.scrollY
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll)
})

</script>
<template>
    <div>
        <InfiniteScroll :data="data" :buffer="500" :manual-after="1">
            <template #previous="{ loading, hasPrevious }">
                <div class="flex flex-row items-center justify-center">
                    <Transition name="fade">
                        <button
                            v-if="scrollY > 2000"
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
                <div class="flex flex-row items-center justify-center">
                    <button
                        v-if="hasMore"
                        @click="fetch"
                        :disabled="loading"
                        class="bottom font-semibold"
                    >
                        {{ loading ? 'Loading...' : 'Load more' }}
                    </button>
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
    background-color: hsl(0, 0%, 100%);
    border-radius: 4px;
}

.top:hover {
    background-color: #FBFBFB;
}

.bottom {
    padding: 6px;
    background-color: hsl(0, 0%, 100%);
    border-radius: 4px;
    border: 2px solid black;
}

.bottom:hover {
    background-color: #FBFBFB;
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
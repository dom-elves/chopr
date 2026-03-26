<script setup>

import { InfiniteScroll, usePage } from '@inertiajs/vue3';
import { onMounted, watch, ref } from 'vue';

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
                    <button v-if="scrollY > 2000" @click="scrollToTop" :disabled="loading" class="top">
                        {{ loading ? 'Loading...' : 'Back to top' }}
                    </button>
                </div>
            </template>
                <slot />
            <template #next="{ loading, fetch, hasMore }">
                <button v-if="hasMore" @click="fetch" :disabled="loading">
                    {{ loading ? 'Loading...' : 'Load more' }}
                </button>
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
</style>
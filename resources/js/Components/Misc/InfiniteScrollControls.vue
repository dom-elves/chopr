<script setup>

import { InfiniteScroll } from '@inertiajs/vue3';

const props = defineProps({
    data: {
        type: String,
        required: true,
    }
});

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

</script>
<template>
    <div>
        <InfiniteScroll :data="data" :buffer="500" :manual-after="1">
            <template #previous="{ loading, hasMore }">
                <div class="flex flex-row items-center justify-center">
                    <button @click="scrollToTop" :disabled="loading" style="position:fixed" class="p-2 rounded border-2 border-solid border-black bg-white">
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
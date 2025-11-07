<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

</script>

<template>
    <Head title="Welcome" />
    <GuestLayout>
        <div class="w-full justify-center" id="links">
            <div v-if="$page.props.auth.user" style="text-align:center">
                <Link
                    :href="route('debt.index')"
                    class="text-xl font-bold uppercase rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                    style="width:110px;"
                >
                    Enter
                </Link>
            </div>
            <div v-else class="flex flex-row justify-center">
                <Link
                    :href="route('login')"
                    class="text-xl font-bold text-right uppercase rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                    style="width:110px;"
                >
                    Log in
                </Link>
                <div style="width:32px;border-right: 1px solid black"></div>
                <div style="width:32px;border-left: 1px solid black"></div>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="text-xl font-bold uppercase rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                    style="width:110px;"
                >
                    Register
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';


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

// write something 
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-slate-100 w-full flex flex-col items-center">
        <header></header>
        <div class="flex flex-col md:w-1/2 bg-white  min-h-screen items-center">
            <div style="max-width:390px" class="flex flex-col items-center">
                <img src="/storage/chopr-logo-2.png" class="mt-20"/>
                <main class="flex flex-row w-full mt-10 justify-center">
                    <Link
                        :href="route('login')"
                        class="text-xl font-bold text-right uppercase rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        style="width:110px;"
                    >
                        Log in
                    </Link>
                    <div style="width:75px"></div>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="text-xl font-bold uppercase rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        style="width:110px;"
                    >
                        Register
                    </Link>
                </main>
                <footer style="position:fixed;bottom:0px"class="flex flex-row w-full p-4 md:w-1/2 justify-center">
                    <a href="https://www.linkedin.com/in/dom-elves-681774200/">
                        <i class="fa-brands fa-linkedin" style="font-size:35px"></i>
                    </a>
                    <div style="width:75px"></div>
                    <a href="https://github.com/dom-elves/chopr">
                        <i class="fa-brands fa-square-github" style="font-size:35px"></i>
                    </a>
                </footer>
            </div>
        </div>    
    </div>
</template>

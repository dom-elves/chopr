<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, getCurrentInstance } from 'vue';

onMounted(() => {
    if (getCurrentInstance().parent.type.__name === 'Welcome') {
        setTimeout(() => {
            document.getElementById('title-image').classList.add('fade-in');
        }, 500);

        setTimeout(() => {
            document.getElementById('beta').classList.add('fade-in');
        }, 500);

        setTimeout(() => {
            document.getElementById('slot-content').classList.add('fade-in');
        }, 1000);

        setTimeout(() => {
            document.getElementById('socials').classList.add('fade-in');
        }, 1500);
    } else {
        document.getElementById('title-image').classList.remove('fade-start');
        document.getElementById('beta').classList.remove('fade-start');
        document.getElementById('slot-content').classList.remove('fade-start');
        document.getElementById('socials').classList.remove('fade-start');
    }
})
</script>

<template>
    <div class="bg-slate-100 w-full flex flex-col items-center">
        <header><!-- put something here one day --></header>
        <main class="flex flex-col md:w-1/2 bg-white  min-h-screen items-center">
            <div style="max-width:390px" class="flex flex-col items-center">
                <a href="/">
                    <img src="/storage/chopr-logo-2.png" class="mt-20 fade-start" id="title-image"/>
                </a>
                <p id="beta" class="fade-start">beta</p>
                <!-- slot is things like registartion, pw recovery -->
                <div id="slot-content" class="fade-start w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                    <slot />
                </div>
                <footer id="socials" style="position:fixed;bottom:0px"class="flex flex-row w-full p-4 md:w-1/2 justify-center fade-start">
                    <a href="https://www.linkedin.com/in/dom-elves-681774200/">
                        <i class="fa-brands fa-linkedin" style="font-size:35px"></i>
                    </a>
                    <div style="width:75px"></div>
                    <a href="https://github.com/dom-elves/chopr">
                        <i class="fa-brands fa-square-github" style="font-size:35px"></i>
                    </a>
                </footer>
            </div>
        </main>
    </div>
</template>
<style scoped>

.fade-start {
  opacity: 0;
  transition: opacity 1s ease-in;
}

.fade-start.fade-in {
  opacity: 1;
}
</style>
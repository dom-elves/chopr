<script setup>
import { ref, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Forms/Dropdown.vue';
import DropdownLink from '@/Components/Forms/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Toast from '@/Components/Misc/Toast.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { currencies } from '@/currencies.js';
import Notifications from '@/Components/Notifications/Notifications.vue';
import MobileNotifications from '@/Components/Notifications/MobileNotifications.vue';
import { useEchoNotification } from "@laravel/echo-vue";
import { useNotificationStore } from '@/Stores/NotificationStore.js';

/*
* As user is auth'd at this point, we need usePage().props.auth.user to subscribe to echo
* So when a new notif comes through, add it to the store
* By default, the store loads with notifs from shared inertia middleware in HandleInertiaRequests
* Any more websockets stuff in the future, will almost certainly live here too
*/
useEchoNotification(
    `App.Models.User.${usePage().props.auth.user.id}`,
    (notification) => {
        useNotificationStore().notifications.unshift({
            id: notification.id,
            type: notification.type,
            // this has to be like this because the component uses data from the model
            // as well as using data from the data property
            data: notification,
            read_at: null,
        });
    },
);

const props = defineProps({
    status: {
        type: String,
        default: '',
    },
});

// this will be part of the eventual exchange rework, choosing to show your balance in whichever currency
// const currency = currencies.find((currency) => currency.code == usePage().props.auth.user.user_balance.currency);
const user_balance = ref(usePage().props.auth.user.balance.amount);

// alternative to using pusher for polling user balance
// as jobs are async on redis, request always beats the job so balance doesn't update
// onMounted(() => {
//     setInterval(() => {
//         router.reload({ only: ['auth'] })
//     }, 5000);
// });

watch( () => usePage().props.auth.user.balance.amount, (newBalance) => {
    user_balance.value = newBalance;
});

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
                style="position:sticky;top:0;z-index:10"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <!-- First half: logo & nav links -->
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('debt.index')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('debt.index')"
                                    :active="route().current('debt.index')"
                                >
                                    Debts
                                </NavLink>
                                <NavLink
                                    :href="route('group.index')"
                                    :active="route().current('group.index')"
                                >
                                    Groups
                                </NavLink>
                            </div>
                        </div> 
                        <!-- Second half: User Balance, Settings/Burger & Notifications -->
                        <div class="flex items-center">
                            <!-- User Balance -->
                            <div class="flex items-center">
                                <div class="flex text-gray-500" title="Your current balance in your default currency">
                                    <!-- bit hacky because obviously vue files can't access brick/money methods -->
                                    <small class="font-semibold" :class="user_balance >= 0 ? 'text-green-500' : 'text-red-500'">£{{ user_balance }}</small>
                                </div>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="hidden sm:flex sm:items-center">
                                <div class="relative">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button
                                                    type="button"
                                                    class="text-center inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                                >
                                                    
                                                    {{ $page.props.auth.user.name }}
                                                    <svg
                                                        class="-me-0.5 ms-2 h-4 w-4"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink
                                                :href="route('profile.edit')"
                                            >
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                            >
                                                Log Out
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>

                            <!-- Hamburger -->
                            <div class="flex items-center sm:hidden">
                                <button
                                    @click="
                                        showingNavigationDropdown =
                                            !showingNavigationDropdown
                                    "
                                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{
                                                hidden: showingNavigationDropdown,
                                                'inline-flex':
                                                    !showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{
                                                hidden: !showingNavigationDropdown,
                                                'inline-flex':
                                                    showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Notifications -->
                            <Notifications class="hidden sm:flex" />
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('debt.index')"
                            :active="route().current('debt.index')"
                        >
                            Debts
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('group.index')"
                            :active="route().current('group.index')"
                        >
                            Groups
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                            <MobileNotifications />
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex flex-col items-center bg-gray-100"> 
                <!-- Toast for displaying messages after operations -->
                <Toast />
                <div class="bg-white w-full md:w-1/2 p-2" style="min-height:calc(100vh - 65px)">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>


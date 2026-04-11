<div
    x-data="{ isMobile: window.innerWidth < 1100 }"
    x-on:resize.window="isMobile = window.innerWidth < 1100"
    class="block md:hidden w-full"
>
    <template x-if="isMobile">
        <x-navbar-weather class="justify-between px-4 py-2 border-t border-gray-100 dark:border-gray-800" />
    </template>
</div>
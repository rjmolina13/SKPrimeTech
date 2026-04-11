<div
    x-data="{ isDesktop: window.innerWidth >= 1100 }"
    x-on:resize.window="isDesktop = window.innerWidth >= 1100"
    class="hidden md:block"
>
    <template x-if="isDesktop">
        <x-navbar-weather class="px-4 py-1 mx-auto absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none" />
    </template>
</div>
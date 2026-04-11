<template>
    <footer
        class="bg-white px-6 py-4 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[0px_-1px_0px_0px_rgba(255,255,255,0.06)]"
    >
        <div class="flex items-center justify-between text-sm text-[#666666] dark:text-[#808080]">
            <span>
                &copy; {{ new Date().getFullYear() }} {{ $t('app.name') }}
            </span>
            <span class="hidden sm:block">
                {{ $t('app.description') }}
            </span>
        </div>
    </footer>
</template>

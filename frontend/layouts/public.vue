<template>
    <div class="flex min-h-screen flex-col bg-white dark:bg-[#0a0a0a]">
        <AppHeader />
        <main class="flex-1 p-4 sm:p-6">
            <slot />
        </main>
        <AppFooter />
    </div>
</template>

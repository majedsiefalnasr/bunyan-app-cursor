<template>
    <div class="flex h-screen flex-col overflow-hidden bg-[#fafafa] dark:bg-[#0a0a0a]">
        <AppHeader />

        <div class="flex flex-1 overflow-hidden">
            <!-- Desktop sidebar -->
            <AppSidebar class="hidden lg:flex" />

            <!-- Mobile drawer -->
            <AppMobileDrawer />

            <!-- Main content -->
            <main
                id="main-content"
                class="flex flex-1 flex-col overflow-y-auto"
            >
                <div class="flex-1 p-4 sm:p-6">
                    <AppBreadcrumb class="mb-4" />
                    <slot />
                </div>
                <AppFooter />
            </main>
        </div>
    </div>
</template>

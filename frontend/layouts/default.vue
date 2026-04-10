<template>
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-200 dark:border-slate-800">
            <nav class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">{{ $t('app.name') }}</h1>
                    <div class="space-x-4 rtl:space-x-reverse">
                        <NuxtLink to="/" class="hover:text-slate-600">
                            {{ $t('nav.home') }}
                        </NuxtLink>
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-1 container mx-auto px-4 py-8">
            <slot />
        </main>

        <footer class="border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 py-8">
            <div class="container mx-auto px-4 text-center text-slate-600 dark:text-slate-400">
                <p>&copy; 2026 {{ $t('app.name') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

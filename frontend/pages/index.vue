<script setup lang="ts">
    const localePath = useLocalePath();
</script>

<template>
    <div class="space-y-8">
        <section class="text-center">
            <h1 class="text-4xl font-bold mb-4">{{ $t('app.name') }}</h1>
            <p class="text-xl text-slate-600 dark:text-slate-400 mb-8">
                {{ $t('app.description') }}
            </p>
            <div class="flex gap-4 justify-center">
                <NuxtLink
                    :to="localePath('/auth/login')"
                    class="rounded-md bg-slate-900 px-6 py-3 text-white hover:bg-slate-800"
                >
                    {{ $t('auth.login') }}
                </NuxtLink>
                <NuxtLink
                    :to="localePath('/auth/register')"
                    class="rounded-md bg-slate-200 px-6 py-3 text-slate-900 hover:bg-slate-300"
                >
                    {{ $t('auth.register') }}
                </NuxtLink>
            </div>
        </section>

        <section class="grid md:grid-cols-3 gap-8">
            <div class="p-8 border border-slate-200 dark:border-slate-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-2">Feature 1</h3>
                <p class="text-slate-600 dark:text-slate-400">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </div>
            <div class="p-8 border border-slate-200 dark:border-slate-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-2">Feature 2</h3>
                <p class="text-slate-600 dark:text-slate-400">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </div>
            <div class="p-8 border border-slate-200 dark:border-slate-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-2">Feature 3</h3>
                <p class="text-slate-600 dark:text-slate-400">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </div>
        </section>
    </div>
</template>

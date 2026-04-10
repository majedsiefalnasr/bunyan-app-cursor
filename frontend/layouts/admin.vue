<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 text-white">
        <nav class="border-b border-slate-700 px-6 py-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold">{{ $t('app.name') }} Admin</h1>
                <button
                    @click="logout"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md transition"
                >
                    {{ $t('auth.logout') }}
                </button>
            </div>
        </nav>

        <div class="container mx-auto px-6 py-8 flex gap-8">
            <aside class="w-64 bg-slate-800 rounded-lg p-6 h-fit">
                <ul class="space-y-2">
                    <li>
                        <NuxtLink
                            to="/admin/dashboard"
                            class="block px-4 py-2 rounded hover:bg-slate-700 transition"
                        >
                            Dashboard
                        </NuxtLink>
                    </li>
                    <li>
                        <NuxtLink to="/admin/users" class="block px-4 py-2 rounded hover:bg-slate-700 transition">
                            Users
                        </NuxtLink>
                    </li>
                </ul>
            </aside>

            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
async function logout() {
    // TODO: Implement logout
}
</script>

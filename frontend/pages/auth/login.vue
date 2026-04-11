<template>
    <form class="space-y-4" @submit.prevent="handleLogin">
        <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-slate-900"
            >
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Password</label>
            <input
                v-model="form.password"
                type="password"
                required
                class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-slate-900"
            >
        </div>
        <button
            type="submit"
            class="w-full px-4 py-2 bg-slate-900 text-white rounded-md hover:bg-slate-800 transition"
        >
            {{ $t('auth.login') }}
        </button>
    </form>

    <div class="mt-4 text-center">
        <p class="text-slate-600">
            {{ $t('auth.register') }}?
            <NuxtLink to="/auth/register" class="text-slate-900 font-semibold hover:underline">
                Sign up here
            </NuxtLink>
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

definePageMeta({
    layout: 'auth',
});

const form = ref({
    email: '',
    password: '',
});

async function handleLogin() {
    // TODO: Implement login
    console.log('Login form submitted:', form.value);
}
</script>

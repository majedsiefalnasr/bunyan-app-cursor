export default defineNuxtPlugin(async () => {
    const auth = useAuthStore();

    // If we have a token from cookie but no hydrated user yet (common on hard refresh / deep link),
    // fetch the profile early so role-based navigation renders correctly.
    if (auth.token && !auth.user) {
        await auth.fetchUser();
    }
});

export function reloadNuxtApp() {
    if (typeof window !== 'undefined') {
        window.location.reload();
    }
}

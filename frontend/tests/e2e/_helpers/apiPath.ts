/**
 * Use as the first argument to `page.route()` instead of a catch-all URL glob.
 * Broad interception breaks `nuxt dev` (Vite HMR, `/_nuxt` chunks) and causes flaky
 * "element is not stable" / detached DOM on submit clicks.
 */
export function isAppRestApiUrl(url: URL): boolean {
    const p = url.pathname;
    return p.startsWith('/v1/') || p.startsWith('/api/v1/');
}

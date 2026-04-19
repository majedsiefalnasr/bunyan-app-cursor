export default defineAppConfig({
    ui: {
        colors: {
            neutral: 'neutral',
        },
        skeleton: {
            base: 'animate-pulse rounded-md bg-elevated',
        },
        modal: {
            slots: {
                overlay: 'fixed inset-0 backdrop-blur-md',
            },
        },
    },
});

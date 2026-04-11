export function useNotification() {
    const toast = useToast();

    const notify = {
        success(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'green' as const,
                icon: 'i-heroicons-check-circle',
            });
        },
        error(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'red' as const,
                icon: 'i-heroicons-x-circle',
            });
        },
        info(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'blue' as const,
                icon: 'i-heroicons-information-circle',
            });
        },
        warning(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'amber' as const,
                icon: 'i-heroicons-exclamation-triangle',
            });
        },
    };

    return { notify };
}

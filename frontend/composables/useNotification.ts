export function useNotification() {
    const toast = useToast();

    const notify = {
        success(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'success',
                icon: 'i-heroicons-check-circle',
            });
        },
        error(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'error',
                icon: 'i-heroicons-x-circle',
            });
        },
        info(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'info',
                icon: 'i-heroicons-information-circle',
            });
        },
        warning(message: string, title?: string) {
            toast.add({
                title: title ?? '',
                description: message,
                color: 'warning',
                icon: 'i-heroicons-exclamation-triangle',
            });
        },
    };

    return { notify };
}

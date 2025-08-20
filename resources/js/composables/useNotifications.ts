import { ref } from 'vue';

export interface NotificationItem {
    id: string;
    title: string;
    message: string;
    type: 'success' | 'error' | 'warning' | 'info';
    duration?: number;
    persistent?: boolean;
}

const notifications = ref<NotificationItem[]>([]);

export function useNotifications() {
    const addNotification = (notification: Omit<NotificationItem, 'id'>) => {
        const id = crypto.randomUUID();
        const newNotification: NotificationItem = {
            id,
            duration: 5000,
            ...notification,
        };

        notifications.value.push(newNotification);

        // Auto remove after duration (if not persistent)
        if (!newNotification.persistent) {
            setTimeout(() => {
                removeNotification(id);
            }, newNotification.duration);
        }

        return id;
    };

    const removeNotification = (id: string) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    };

    const clearAll = () => {
        notifications.value = [];
    };

    // Convenience methods
    const success = (title: string, message: string, options?: Partial<NotificationItem>) => {
        return addNotification({ title, message, type: 'success', ...options });
    };

    const error = (title: string, message: string, options?: Partial<NotificationItem>) => {
        return addNotification({ title, message, type: 'error', ...options });
    };

    const warning = (title: string, message: string, options?: Partial<NotificationItem>) => {
        return addNotification({ title, message, type: 'warning', ...options });
    };

    const info = (title: string, message: string, options?: Partial<NotificationItem>) => {
        return addNotification({ title, message, type: 'info', ...options });
    };

    return {
        notifications,
        addNotification,
        removeNotification,
        clearAll,
        success,
        error,
        warning,
        info,
    };
}

import { router } from '@inertiajs/vue3';

/**
 * Helper functions untuk menampilkan flash messages di frontend
 */

interface FlashOptions {
    preserveScroll?: boolean;
    preserveState?: boolean;
    only?: string[];
    except?: string[];
}

export const flashSuccess = (message: string, route?: string, options?: FlashOptions) => {
    if (route) {
        router.visit(route, {
            method: 'get',
            data: { flash_success: message },
            ...options
        });
    } else {
        // Untuk menampilkan pesan tanpa redirect
        window.history.replaceState(
            { ...window.history.state, flash: { success: message } },
            '',
            window.location.href
        );
        // Trigger a custom event to notify components
        window.dispatchEvent(new CustomEvent('flash-message', {
            detail: { type: 'success', message }
        }));
    }
};

export const flashError = (message: string, route?: string, options?: FlashOptions) => {
    if (route) {
        router.visit(route, {
            method: 'get',
            data: { flash_error: message },
            ...options
        });
    } else {
        window.history.replaceState(
            { ...window.history.state, flash: { error: message } },
            '',
            window.location.href
        );
        window.dispatchEvent(new CustomEvent('flash-message', {
            detail: { type: 'error', message }
        }));
    }
};

export const flashInfo = (message: string, route?: string, options?: FlashOptions) => {
    if (route) {
        router.visit(route, {
            method: 'get',
            data: { flash_info: message },
            ...options
        });
    } else {
        window.history.replaceState(
            { ...window.history.state, flash: { info: message } },
            '',
            window.location.href
        );
        window.dispatchEvent(new CustomEvent('flash-message', {
            detail: { type: 'info', message }
        }));
    }
};

export const flashWarning = (message: string, route?: string, options?: FlashOptions) => {
    if (route) {
        router.visit(route, {
            method: 'get',
            data: { flash_warning: message },
            ...options
        });
    } else {
        window.history.replaceState(
            { ...window.history.state, flash: { warning: message } },
            '',
            window.location.href
        );
        window.dispatchEvent(new CustomEvent('flash-message', {
            detail: { type: 'warning', message }
        }));
    }
};

export const flashMessage = (message: string, route?: string, options?: FlashOptions) => {
    if (route) {
        router.visit(route, {
            method: 'get',
            data: { flash_message: message },
            ...options
        });
    } else {
        window.history.replaceState(
            { ...window.history.state, flash: { message } },
            '',
            window.location.href
        );
        window.dispatchEvent(new CustomEvent('flash-message', {
            detail: { type: 'message', message }
        }));
    }
};

/**
 * Show flash message instantly without page navigation
 */
export const showFlash = {
    success: (message: string) => flashSuccess(message),
    error: (message: string) => flashError(message),
    info: (message: string) => flashInfo(message),
    warning: (message: string) => flashWarning(message),
    message: (message: string) => flashMessage(message),
};

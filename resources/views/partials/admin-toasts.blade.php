@php
    $toasts = [];

    $resolveMessage = static function (mixed $message): string {
        if (is_array($message)) {
            $key = $message['key'] ?? null;
            $replace = is_array($message['replace'] ?? null) ? $message['replace'] : [];
            return is_string($key) ? __($key, $replace) : '';
        }
        if (!is_scalar($message)) return '';
        $message = (string) $message;
        if (\Illuminate\Support\Facades\Lang::has($message)) return __($message);
        return $message;
    };

    $pushToast = static function (array &$items, string $type, mixed $message, array $details = []) use ($resolveMessage): void {
        if (blank($message)) return;
        $items[] = ['type' => $type, 'message' => $resolveMessage($message), 'details' => $details];
    };

    $pushToast($toasts, 'success', session('success'));
    $pushToast($toasts, 'success', session('ok'));
    $pushToast($toasts, 'error',   session('error'));
    $pushToast($toasts, 'warning', session('warning'));
    $pushToast($toasts, 'info',    session('info'));

    if (session()->has('status')) {
        $statusMessage = match (session('status')) {
            'profile-updated'          => 'messages.status.profile_updated',
            'password-updated'         => 'messages.status.password_updated',
            'verification-link-sent'   => 'messages.status.verification_link_sent',
            default                    => session('status'),
        };
        $pushToast($toasts, 'info', $statusMessage);
    }

    if (isset($errors) && $errors->any()) {
        $details = array_map($resolveMessage, $errors->all());
        $toasts[] = [
            'type'    => 'error',
            'message' => __('messages.error.validation_summary'),
            'details' => $details,
        ];
    }
@endphp

{{-- Seed the Alpine store from server-side flash data --}}
<div x-data x-init="$store.toasts.initFromServer(@js($toasts))"></div>

{{-- Toast container --}}
<div class="pointer-events-none fixed left-1/2 top-4 z-[1000] flex w-[min(94vw,34rem)] -translate-x-1/2 flex-col items-stretch gap-3"
     data-system-toast-container
     aria-live="polite" aria-atomic="false" x-data>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-5 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-250"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="-translate-y-3 opacity-0 scale-95"
             class="pointer-events-auto relative overflow-hidden rounded-2xl border px-4 py-3 shadow-2xl backdrop-blur-xl"
             :class="{
                 'border-emerald-200/80 bg-emerald-600 text-white shadow-emerald-950/15 dark:border-emerald-400/25 dark:bg-emerald-600 dark:text-white': toast.type === 'success',
                 'border-rose-200/90 bg-rose-600 text-white shadow-rose-950/15 dark:border-rose-400/25 dark:bg-rose-600 dark:text-white': toast.type === 'error',
                 'border-amber-200/90 bg-amber-500 text-white shadow-amber-950/15 dark:border-amber-400/25 dark:bg-amber-500 dark:text-white': toast.type === 'warning',
                 'border-blue-200/90 bg-blue-600 text-white shadow-blue-950/15 dark:border-blue-400/25 dark:bg-blue-600 dark:text-white': toast.type === 'info',
             }"
             role="alert">

            <div class="flex items-start gap-3 pr-8">
                <span class="mt-0.5 inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/18 text-white ring-1 ring-white/25">
                    <svg x-show="toast.type==='success'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="toast.type==='error'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                    </svg>
                    <svg x-show="toast.type==='warning'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z"/>
                    </svg>
                    <svg x-show="toast.type==='info'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold leading-6" x-text="toast.message"></p>
                    <template x-if="toast.details && toast.details.length">
                        <ul class="mt-1 max-h-28 list-disc space-y-0.5 overflow-y-auto pl-4 text-xs leading-5 text-white/85">
                            <template x-for="detail in toast.details.slice(0,4)" :key="detail">
                                <li x-text="detail"></li>
                            </template>
                        </ul>
                    </template>
                </div>
            </div>

            <button type="button"
                    @click="$store.toasts.dismiss(toast.id)"
                    class="absolute right-3 top-3 inline-flex h-7 w-7 items-center justify-center rounded-full text-white/75 transition hover:bg-white/15 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/40"
                    aria-label="Dismiss">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

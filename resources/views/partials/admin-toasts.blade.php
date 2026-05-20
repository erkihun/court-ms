@php
    $toasts = [];

    $resolveMessage = static function (mixed $message): string {
        if (is_array($message)) {
            $key = $message['key'] ?? null;
            $replace = is_array($message['replace'] ?? null) ? $message['replace'] : [];

            return is_string($key) ? __($key, $replace) : '';
        }

        if (!is_scalar($message)) {
            return '';
        }

        $message = (string) $message;

        if (\Illuminate\Support\Facades\Lang::has($message)) {
            return __($message);
        }

        return $message;
    };

    $pushToast = static function (array &$items, string $type, mixed $message) use ($resolveMessage): void {
        if (blank($message)) {
            return;
        }

        $items[] = [
            'type' => $type,
            'message' => $resolveMessage($message),
        ];
    };

    $pushToast($toasts, 'success', session('success'));
    $pushToast($toasts, 'success', session('ok'));
    $pushToast($toasts, 'error', session('error'));
    $pushToast($toasts, 'warning', session('warning'));
    $pushToast($toasts, 'info', session('info'));

    if (session()->has('status')) {
        $statusMessage = match (session('status')) {
            'profile-updated' => 'messages.status.profile_updated',
            'password-updated' => 'messages.status.password_updated',
            'verification-link-sent' => 'messages.status.verification_link_sent',
            default => session('status'),
        };

        $pushToast($toasts, 'info', $statusMessage);
    }

    if (isset($errors) && $errors->any()) {
        $toasts[] = [
            'type' => 'error',
            'message' => __('messages.error.validation_summary'),
            'details' => array_map($resolveMessage, $errors->all()),
        ];
    }

    $styles = [
        'success' => [
            'wrap' => 'border-emerald-200/80 bg-white/95 text-emerald-950 shadow-emerald-950/10 dark:border-emerald-400/25 dark:bg-slate-950/95 dark:text-emerald-100',
            'icon' => 'bg-emerald-500 text-white',
            'accent' => 'bg-emerald-500',
        ],
        'error' => [
            'wrap' => 'border-rose-200/90 bg-white/95 text-rose-950 shadow-rose-950/10 dark:border-rose-400/25 dark:bg-slate-950/95 dark:text-rose-100',
            'icon' => 'bg-rose-600 text-white',
            'accent' => 'bg-rose-600',
        ],
        'warning' => [
            'wrap' => 'border-amber-200/90 bg-white/95 text-amber-950 shadow-amber-950/10 dark:border-amber-400/25 dark:bg-slate-950/95 dark:text-amber-100',
            'icon' => 'bg-amber-500 text-white',
            'accent' => 'bg-amber-500',
        ],
        'info' => [
            'wrap' => 'border-blue-200/90 bg-white/95 text-blue-950 shadow-blue-950/10 dark:border-blue-400/25 dark:bg-slate-950/95 dark:text-blue-100',
            'icon' => 'bg-blue-600 text-white',
            'accent' => 'bg-blue-600',
        ],
    ];
@endphp

@if(!empty($toasts))
<div class="pointer-events-none fixed left-1/2 top-4 z-[1000] flex w-[min(94vw,34rem)] -translate-x-1/2 flex-col items-stretch gap-3" aria-live="polite" aria-atomic="true">
    @foreach($toasts as $index => $toast)
        @php
            $type = $toast['type'] ?? 'info';
            $style = $styles[$type] ?? $styles['info'];
        @endphp
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, {{ 4300 + ($index * 350) }})"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-y-5 opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-250"
            x-transition:leave-start="translate-y-0 opacity-100 scale-100"
            x-transition:leave-end="-translate-y-3 opacity-0 scale-95"
            class="pointer-events-auto relative overflow-hidden rounded-2xl border px-4 py-3 shadow-2xl backdrop-blur-xl {{ $style['wrap'] }}"
            role="status">
            <span class="absolute inset-y-0 left-0 w-1 {{ $style['accent'] }}" aria-hidden="true"></span>
            <div class="flex items-start gap-3 pl-1">
                <span class="mt-0.5 inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full {{ $style['icon'] }}" aria-hidden="true">
                    @if($type === 'success')
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7"/></svg>
                    @elseif($type === 'error')
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                    @elseif($type === 'warning')
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z"/></svg>
                    @else
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z"/></svg>
                    @endif
                </span>
                <div class="min-w-0 flex-1 pr-7">
                    <div class="text-sm font-semibold leading-6">{{ $toast['message'] }}</div>
                    @if(!empty($toast['details']))
                        <ul class="mt-1 max-h-28 list-disc space-y-0.5 overflow-y-auto pl-4 text-xs leading-5 opacity-80">
                            @foreach(array_slice($toast['details'], 0, 4) as $detail)
                                <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <button type="button" @click="show = false" class="absolute right-3 top-3 inline-flex h-7 w-7 items-center justify-center rounded-full text-current opacity-55 transition hover:bg-black/5 hover:opacity-90 dark:hover:bg-white/10" aria-label="{{ __('Close') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endforeach
</div>
@endif

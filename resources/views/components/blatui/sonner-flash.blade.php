
@php
    $flashes = [];

    foreach (['success', 'error', 'warning', 'info'] as $key) {
        if (session()->has($key)) {
            $flashes[] = ['type' => $key, 'description' => (string) session($key)];
        }
    }
    if (session()->has('message')) {
        $flashes[] = ['type' => 'info', 'description' => (string) session('message')];
    }

    $statusDefaults = [
        'verification-link-sent' => __('A new verification link has been emailed to you.'),
        'profile-information-updated' => __('Profile updated.'),
        'password-updated' => __('Password updated.'),
        'two-factor-authentication-enabled' => __('Two-factor authentication enabled.'),
        'two-factor-authentication-confirmed' => __('Two-factor authentication confirmed.'),
        'two-factor-authentication-disabled' => __('Two-factor authentication disabled.'),
        'recovery-codes-generated' => __('Recovery codes generated.'),
        'password-confirmed' => __('Password confirmed.'),
    ];
    if (session()->has('status')) {
        $s = (string) session('status');
        $translated = __($s);

        if ($translated !== $s) {
            $flashes[] = ['type' => 'success', 'description' => $translated];
        } elseif (isset($statusDefaults[$s])) {
            $flashes[] = ['type' => 'success', 'description' => $statusDefaults[$s]];
        } elseif (str_contains($s, ' ')) {
            $flashes[] = ['type' => 'success', 'description' => $s];
        }
    }
@endphp

@if (! empty($flashes))
    <div
        data-slot="sonner-flash"
        x-data
        x-init="$nextTick(() => { @foreach ($flashes as $f) $dispatch('toast', @js($f)); @endforeach })"
        class="hidden"
        aria-hidden="true"
    ></div>
@endif

@php($user = filament()->auth()->user())

@if ($user)
    <div class="fi-admin-topbar-user">
        <div class="fi-admin-topbar-user-copy">
            <div class="fi-admin-topbar-user-name">
                {{ $user->name }}
            </div>

            <div class="fi-admin-topbar-user-status">
                {{ $user->hasVerifiedEmail() ? 'Terverifikasi ✓' : 'Menunggu verifikasi' }}
            </div>
        </div>
    </div>
@endif

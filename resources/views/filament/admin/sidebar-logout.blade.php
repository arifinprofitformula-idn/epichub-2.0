<div class="fi-admin-sidebar-logout-wrap">
    <form
        action="{{ filament()->getLogoutUrl() }}"
        method="post"
        class="fi-admin-sidebar-logout-form"
    >
        @csrf

        <button
            type="submit"
            class="fi-admin-sidebar-logout-btn"
        >
            <svg viewBox="0 0 24 24" fill="none" class="fi-admin-sidebar-logout-icon" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M14 16.5L18.5 12L14 7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18 12H9.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                <path d="M9.75 5.75H7.75C6.64543 5.75 5.75 6.64543 5.75 7.75V16.25C5.75 17.3546 6.64543 18.25 7.75 18.25H9.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>

            <span>Logout</span>
        </button>
    </form>
</div>

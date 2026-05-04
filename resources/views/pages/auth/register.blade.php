<x-layouts::auth :title="__('Register')">
    <div
        class="flex flex-col gap-4 sm:gap-6"
        x-data="{
            password: @js(old('password', '')),
            passwordConfirmation: @js(old('password_confirmation', '')),
            hasMinLength() {
                return this.password.length >= 8;
            },
            hasMixedCase() {
                return /[a-z]/.test(this.password) && /[A-Z]/.test(this.password);
            },
            hasNumber() {
                return /\d/.test(this.password);
            },
            hasSymbol() {
                return /[^A-Za-z0-9]/.test(this.password);
            },
            strengthScore() {
                return [
                    this.hasMinLength(),
                    this.hasMixedCase(),
                    this.hasNumber(),
                    this.hasSymbol(),
                ].filter(Boolean).length;
            },
            strengthPercent() {
                if (! this.password.length) {
                    return 0;
                }

                return Math.max(20, this.strengthScore() * 25);
            },
            strengthTone() {
                if (this.strengthScore() <= 1) {
                    return 'weak';
                }

                if (this.strengthScore() <= 3) {
                    return 'medium';
                }

                return 'strong';
            },
            strengthLabel() {
                if (this.strengthScore() <= 1) {
                    return 'Lemah';
                }

                if (this.strengthScore() <= 3) {
                    return 'Sedang';
                }

                return 'Kuat';
            },
            allRequirementsMet() {
                return this.hasMinLength() && this.hasMixedCase() && this.hasNumber() && this.hasSymbol();
            },
            showPasswordGuidance() {
                return this.password.length > 0;
            },
            confirmationFilled() {
                return this.passwordConfirmation.length > 0;
            },
            passwordsMatch() {
                return this.confirmationFilled() && this.password === this.passwordConfirmation;
            },
            passwordsMismatch() {
                return this.confirmationFilled() && this.password !== this.passwordConfirmation;
            },
        }"
    >
        <x-auth-header :title="'Buat Akun Baru'" :description="'Daftar untuk mulai mengakses program belajar Anda.'" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="mx-auto flex w-full flex-col gap-3.5 sm:gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="'Nama Lengkap'"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="'Nama lengkap'"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'Alamat Email'"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@anda.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="'Password'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'Buat password'"
                x-model="password"
                viewable
            />

            <div x-cloak x-show="showPasswordGuidance()" x-transition class="-mt-1 rounded-[1.1rem] border border-slate-200/80 bg-white/75 p-3 shadow-[0_12px_26px_rgba(15,23,42,0.05)] sm:-mt-2 sm:rounded-[1.35rem] sm:p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-slate-400 sm:text-[0.68rem]">Kekuatan Password</div>
                        <div
                            class="mt-0.5 text-xs font-semibold sm:mt-1 sm:text-sm"
                            :class="{
                                'text-rose-600': strengthTone() === 'weak',
                                'text-amber-600': strengthTone() === 'medium',
                                'text-emerald-600': strengthTone() === 'strong',
                            }"
                            x-text="strengthLabel()"
                        ></div>
                    </div>

                    <div
                        class="rounded-full px-2 py-0.5 text-[0.6rem] font-semibold uppercase tracking-[0.14em] sm:px-3 sm:py-1 sm:text-[0.68rem]"
                        :class="{
                            'bg-rose-50 text-rose-600': strengthTone() === 'weak',
                            'bg-amber-50 text-amber-600': strengthTone() === 'medium',
                            'bg-emerald-50 text-emerald-600': strengthTone() === 'strong',
                        }"
                        x-text="`${strengthScore()}/4 aturan terpenuhi`"
                    ></div>
                </div>

                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 sm:mt-4 sm:h-2.5">
                    <div
                        class="h-full rounded-full transition-all duration-300"
                        :class="{
                            'bg-rose-500': strengthTone() === 'weak',
                            'bg-amber-400': strengthTone() === 'medium',
                            'bg-emerald-500': strengthTone() === 'strong',
                        }"
                        :style="`width: ${strengthPercent()}%`"
                    ></div>
                </div>

                <div class="mt-2 grid gap-1.5 sm:mt-4 sm:gap-2">
                    <div class="epi-password-rule" :data-met="hasMinLength()">
                        <span class="epi-password-rule-icon" :data-met="hasMinLength()">
                            <span x-show="!hasMinLength()">!</span>
                            <span x-show="hasMinLength()">✓</span>
                        </span>
                        <span>Minimal 8 karakter</span>
                    </div>

                    <div class="epi-password-rule" :data-met="hasMixedCase()">
                        <span class="epi-password-rule-icon" :data-met="hasMixedCase()">
                            <span x-show="!hasMixedCase()">!</span>
                            <span x-show="hasMixedCase()">✓</span>
                        </span>
                        <span>Memiliki huruf besar dan huruf kecil</span>
                    </div>

                    <div class="epi-password-rule" :data-met="hasNumber()">
                        <span class="epi-password-rule-icon" :data-met="hasNumber()">
                            <span x-show="!hasNumber()">!</span>
                            <span x-show="hasNumber()">✓</span>
                        </span>
                        <span>Memiliki minimal 1 angka</span>
                    </div>

                    <div class="epi-password-rule" :data-met="hasSymbol()">
                        <span class="epi-password-rule-icon" :data-met="hasSymbol()">
                            <span x-show="!hasSymbol()">!</span>
                            <span x-show="hasSymbol()">✓</span>
                        </span>
                        <span>Memiliki minimal 1 simbol</span>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="'Konfirmasi Password'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'Ulangi password'"
                x-model="passwordConfirmation"
                x-bind:data-invalid="passwordsMismatch()"
                x-bind:data-password-match="passwordsMatch()"
                viewable
            />

            <div x-cloak x-show="confirmationFilled()" x-transition class="-mt-1 sm:-mt-2">
                <div
                    class="flex items-center gap-2 rounded-[1rem] border px-3 py-2 text-xs font-medium transition-all duration-200 sm:gap-3 sm:rounded-[1.1rem] sm:px-3.5 sm:py-3 sm:text-sm"
                    :class="{
                        'border-rose-200 bg-rose-50 text-rose-700 animate-pulse': passwordsMismatch(),
                        'border-emerald-200 bg-emerald-50 text-emerald-700': passwordsMatch(),
                    }"
                >
                    <span
                        class="flex size-5 shrink-0 items-center justify-center rounded-full text-xs font-bold sm:size-7 sm:text-sm"
                        :class="{
                            'bg-rose-100 text-rose-600': passwordsMismatch(),
                            'bg-emerald-100 text-emerald-600': passwordsMatch(),
                        }"
                    >
                        <span x-show="passwordsMismatch()">!</span>
                        <span x-show="passwordsMatch()">✓</span>
                    </span>

                    <span x-show="passwordsMismatch()">Konfirmasi password belum cocok. Pastikan penulisannya sama persis.</span>
                    <span x-show="passwordsMatch()">Konfirmasi password sudah cocok.</span>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <flux:button type="submit" variant="primary" class="epi-auth-btn w-full" data-test="register-user-button">
                    DAFTAR SEKARANG
                </flux:button>
            </div>

            <x-referral-info-card
                :channel="data_get($referralInfo ?? [], 'channel')"
                :source="data_get($referralInfo ?? [], 'source', 'default_system')"
                :locked="(bool) data_get($referralInfo ?? [], 'is_locked', false)"
                context="register"
                class="-mt-2"
            />
        </form>

        <div class="mx-auto w-full border-t border-slate-200 pt-4 text-center text-sm text-slate-500 sm:pt-7">
            <span class="uppercase tracking-wide">Sudah punya akun?</span>
            <flux:link class="epi-auth-link ml-1 font-semibold uppercase tracking-wide" :href="route('login')">Masuk</flux:link>
        </div>
    </div>
</x-layouts::auth>
